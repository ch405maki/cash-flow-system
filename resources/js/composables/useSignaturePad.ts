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
      
      // IMPORTANT: createConnection expects (url, onOpen, onClose, onError)
      // NOT an object!
      commons.createConnection(
        'wss://localhost:49494',  // URL
        () => {                    // onOpen
          console.log('WebSocket connected successfully')
          isConnected.value = true
          
          // Search for pads after connection
          searchForPads().then(() => {
            resolve()
          }).catch(reject)
        },
        () => {                    // onClose
          console.log('WebSocket closed')
          isConnected.value = false
          isPadOpen.value = false
        },
        (err: any) => {            // onError
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
            // Find Sigma USB pad by serial number
            const targetPad = result.foundPads.find((pad: any) => 
              pad.serialNumber === '1000401383'
            )
            
            if (targetPad) {
              console.log('Found target pad:', targetPad)
              padIndex = targetPad.index
              openPad(padIndex).then(resolve).catch(reject)
            } else {
              // Use first available pad
              console.log('Using first available pad:', result.foundPads[0])
              padIndex = result.foundPads[0].index
              openPad(padIndex).then(resolve).catch(reject)
            }
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

      // Set up event handlers
      defaults.handleConfirmSignature = () => {
        console.log('Signature confirmed')
        
        // Get signature data after confirmation
        const signDataParams = new defaults.Params.getSignatureData()
        defaults.getSignatureData(signDataParams)
          .then((dataResult: any) => {
            console.log('Signature data:', dataResult)
            
            // Get signature image
            const imageParams = new defaults.Params.getSignatureImage()
            imageParams.setFileType(defaults.FileType.PNG)
            defaults.getSignatureImage(imageParams)
              .then((imageResult: any) => {
                console.log('Signature image received')
                isSigning.value = false
                resolve({
                  imageData: imageResult.file,
                  signaturePoints: dataResult.signData,
                  signatureTime: Date.now()
                })
              })
              .catch((err: any) => {
                console.error('Failed to get signature image:', err)
                isSigning.value = false
                reject(new Error(err.message || 'Failed to get signature image'))
              })
          })
          .catch((err: any) => {
            console.error('Failed to get signature data:', err)
            isSigning.value = false
            reject(new Error(err.message || 'Failed to get signature data'))
          })
      }

      defaults.handleCancelSignature = () => {
        console.log('Signature cancelled')
        isSigning.value = false
        reject(new Error('Cancelled'))
      }

      defaults.handleRetrySignature = () => {
        console.log('Signature retry')
      }

      // Start signature
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

  const confirmSignature = () => {
    const defaults = getDefault()
    if (defaults && isSigning.value) {
      defaults.confirmSignature()
        .catch((err: any) => console.warn('Confirm error:', err))
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
    confirmSignature,
    disconnect
  }
}