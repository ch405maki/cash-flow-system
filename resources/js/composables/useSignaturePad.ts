// resources/js/composables/useSignaturePad.ts
import { ref } from 'vue'

declare global {
  interface Window {
    STPadServerLib: any
  }
}

export interface SignatureResult {
  imageData: string
  signaturePoints?: any
  signatureTime?: number
}

export function useSignaturePad() {
  const isConnected = ref(false)
  const isPadOpen = ref(false)
  const isSigning = ref(false)
  const error = ref<string | null>(null)
  const padInfo = ref<any>(null)
  let padIndex: number | null = null

  const getCommons = () => window.STPadServerLib?.STPadServerLibCommons
  const getDefault = () => window.STPadServerLib?.STPadServerLibDefault

  const connect = (): Promise<void> => {
    return new Promise((resolve, reject) => {
      const commons = getCommons()
      const defaults = getDefault()
      
      if (!commons || !defaults) {
        reject(new Error('STPadServerLib not loaded correctly'))
        return
      }

      console.log('Creating WebSocket connection to wss://localhost:49494...')
      
      commons.createConnection(
        'wss://localhost:49494',
        () => {
          console.log('WebSocket connected successfully')
          isConnected.value = true
          searchForPads().then(() => resolve()).catch(reject)
        },
        () => {
          console.log('WebSocket closed')
          isConnected.value = false
          isPadOpen.value = false
        },
        (err: any) => {
          console.error('WebSocket error:', err)
          error.value = err.message || 'Connection failed'
          reject(new Error(error.value))
        }
      )
    })
  }

  const searchForPads = (): Promise<void> => {
    return new Promise((resolve, reject) => {
      const defaults = getDefault()
      
      if (!defaults) {
        reject(new Error('Library not loaded'))
        return
      }

      console.log('Searching for pads...')
      
      const params = new defaults.Params.searchForPads()
      
      defaults.searchForPads(params)
        .then((result: any) => {
          console.log('Search result:', result)
          
          if (result.foundPads && result.foundPads.length > 0) {
            const targetPad = result.foundPads.find((pad: any) => 
              pad.serialNumber === '1000401383'
            )
            
            if (targetPad) {
              console.log('Found target pad:', targetPad)
              padIndex = targetPad.index
            } else {
              console.log('Using first available pad:', result.foundPads[0])
              padIndex = result.foundPads[0].index
            }
            openPad(padIndex!).then(resolve).catch(reject)
          } else {
            reject(new Error('No signature pads found'))
          }
        })
        .catch((err: any) => {
          console.error('Search failed:', err)
          reject(new Error(err.message || 'Failed to search for pads'))
        })
    })
  }

  const openPad = (index: number): Promise<void> => {
    return new Promise((resolve, reject) => {
      const defaults = getDefault()
      
      if (!defaults) {
        reject(new Error('Library not loaded'))
        return
      }

      console.log('Opening pad at index:', index)
      
      const params = new defaults.Params.openPad(index)
      
      defaults.openPad(params)
        .then((result: any) => {
          console.log('Pad opened:', result)
          isPadOpen.value = true
          if (result.padInfo) {
            padInfo.value = result.padInfo
          }
          resolve()
        })
        .catch((err: any) => {
          console.error('Open pad failed:', err)
          reject(new Error(err.message || 'Failed to open pad'))
        })
    })
  }

  const startSigning = (promptText = 'Please sign to authorize release'): Promise<SignatureResult> => {
    return new Promise((resolve, reject) => {
      if (!isConnected.value || !isPadOpen.value || padIndex === null) {
        reject(new Error('Pad not connected or opened'))
        return
      }

      const defaults = getDefault()
      
      if (!defaults) {
        reject(new Error('Library not loaded'))
        return
      }

      console.log('Starting signature...')
      isSigning.value = true

      // User pressed confirm on the device - capture may have already ended, try to get image
      defaults.handleConfirmSignature = () => {
        console.log('Signature confirmed on device, getting image...')
        
        const errorHandler = (msg: string, url: string, line: number, col: number, error: Error) => {
          if (msg && msg.includes("Cannot read properties of null")) {
            console.warn('Ignored library parsing error:', msg)
            return true
          }
          return false
        }
        
        const originalOnError = window.onerror
        window.onerror = errorHandler
        
        // Wait initial time for device to process, then try
        const tryGetImage = (attempt = 1): Promise<any> => {
          return new Promise((resolve, reject) => {
            console.log(`Attempt ${attempt} to get signature image...`)
            
            const imageParams = new defaults.Params.getSignatureImage()
            imageParams.setFileType(defaults.FileType.PNG)
            
            defaults.getSignatureImage(imageParams)
              .then((result: any) => {
                console.log('Got signature image, returnCode:', result?.returnCode)
                resolve(result)
              })
              .catch((err: any) => {
                console.log(`Get image error: ${err?.returnCode} - ${err?.errorMessage}`)
                
                // Any error - retry after delay (capture might still be processing)
                if (attempt < 4) {
                  const delay = attempt * 400
                  console.log(`Retrying after ${delay}ms...`)
                  setTimeout(() => {
                    tryGetImage(attempt + 1).then(resolve).catch(reject)
                  }, delay)
                } else {
                  reject(err)
                }
              })
          })
        }
        
        // Wait for device to finish processing, then stop and get image
        setTimeout(() => {
          // Stop signature after short delay (device needs time to process confirmation)
          setTimeout(() => {
            defaults.stopSignature()
              .then(() => {
                console.log('Signature capture stopped')
              })
              .catch((err: any) => {
                console.log('Stop signature warning:', err?.returnCode)
              })
          }, 300)

          // Try to get image after ~600ms total (gives device time to stop)
          setTimeout(() => {
            tryGetImage()
              .then((imageResult: any) => {
                console.log('Signature image received, returnCode:', imageResult?.returnCode)
                isSigning.value = false
                window.onerror = originalOnError
                resolve({
                  imageData: imageResult.file,
                  signaturePoints: null,
                  signatureTime: Date.now()
                })
              })
              .catch((err: any) => {
                console.error('Failed to get signature:', err)
                isSigning.value = false
                window.onerror = originalOnError
                reject(new Error(err?.errorMessage || err?.message || 'Failed to get signature'))
              })
          }, 600)
        }, 500)
      }

      defaults.handleCancelSignature = () => {
        console.log('Signature cancelled on device')
        isSigning.value = false
        reject(new Error('Cancelled'))
      }

      defaults.handleRetrySignature = () => {
        console.log('User chose retry on device')
        // Don't resolve/reject - let user sign again
      }

      const params = new defaults.Params.startSignature()
      params.setCustomText(promptText)
      
      defaults.startSignature(params)
        .catch((err: any) => {
          console.error('Start signature failed:', err)
          isSigning.value = false
          reject(new Error(err.message || 'Failed to start signature'))
        })
    })
  }

  const cancelSigning = () => {
    const defaults = getDefault()
    if (defaults && isSigning.value) {
      defaults.cancelSignature()
        .catch((err: any) => console.warn('Cancel error:', err))
      isSigning.value = false
    }
  }

  const disconnect = () => {
    const defaults = getDefault()
    const commons = getCommons()
    
    if (defaults && padIndex !== null) {
      const params = new defaults.Params.closePad(padIndex)
      defaults.closePad(params)
        .catch((err: any) => console.warn('Close pad error:', err))
    }
    
    if (commons) {
      commons.destroyConnection()
    }
    
    isConnected.value = false
    isPadOpen.value = false
    padIndex = null
  }

  return {
    isConnected,
    isPadOpen,
    isSigning,
    error,
    padInfo,
    connect,
    startSigning,
    cancelSigning,
    disconnect
  }
}