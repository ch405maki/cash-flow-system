// composables/usePettyCash.ts
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { usePage } from '@inertiajs/vue3'

export function usePettyCash() {
  const toast = useToast()
  const page = usePage()

  const updatePettyCash = async (id: number, data: FormData) => {
    return new Promise((resolve, reject) => {
      router.post(`/petty-cash/${id}`, data, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
          // Get the updated data from the flashed session
          const updatedPettyCash = page.props.pettyCash || response.props?.pettyCash
          const savedItem = page.props.saved_item || response.props?.saved_item
          
          toast.success('Changes saved successfully!')
          
          if (savedItem) {
            resolve(savedItem)
          } else {
            resolve(updatedPettyCash)
          }
        },
        onError: (errors) => {
          console.error('Update error:', errors)
          toast.error('Failed to save changes.')
          reject(errors)
        }
      })
    })
  }

  const updateItem = async (pettyCashId: number, itemData: any) => {
    const data = new FormData()
    data.append('_method', 'PUT')
    
    // Add item data
    Object.keys(itemData).forEach(key => {
      if (key !== 'receipt' && itemData[key] !== null && itemData[key] !== undefined) {
        data.append(`items[0][${key}]`, itemData[key].toString())
      }
    })
    
    if (itemData.receipt instanceof File) {
      data.append(`items[0][receipt]`, itemData.receipt)
    }

    return updatePettyCash(pettyCashId, data)
  }

  const deleteItem = async (pettyCashId: number, itemId: number) => {
    const data = new FormData()
    data.append('_method', 'PUT')
    data.append('deleted_items[0]', itemId.toString())
    
    return updatePettyCash(pettyCashId, data)
  }

  const addItem = async (pettyCashId: number, itemData: any) => {
    const data = new FormData()
    data.append('_method', 'PUT')
    
    Object.keys(itemData).forEach(key => {
      if (key !== 'receipt' && itemData[key] !== null && itemData[key] !== undefined) {
        data.append(`items[0][${key}]`, itemData[key].toString())
      }
    })
    
    if (itemData.receipt instanceof File) {
      data.append(`items[0][receipt]`, itemData.receipt)
    }

    return updatePettyCash(pettyCashId, data)
  }

  const updateMainFields = async (pettyCashId: number, fields: any) => {
    const data = new FormData()
    data.append('_method', 'PUT')
    
    Object.keys(fields).forEach(key => {
      if (fields[key] !== null && fields[key] !== undefined) {
        data.append(key, fields[key].toString())
      }
    })

    return updatePettyCash(pettyCashId, data)
  }

  return {
    updatePettyCash,
    updateItem,
    deleteItem,
    addItem,
    updateMainFields
  }
}