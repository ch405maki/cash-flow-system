import { ref, watch } from 'vue'

const WATERMARK_STORAGE_KEY = 'app-watermark-enabled'

// Create a reactive state that persists across components
const isWatermarkEnabled = ref(localStorage.getItem(WATERMARK_STORAGE_KEY) !== 'false')

// Watch for changes and save to localStorage
watch(isWatermarkEnabled, (newValue) => {
  localStorage.setItem(WATERMARK_STORAGE_KEY, String(newValue))
})

export function useWatermark() {
  const toggleWatermark = () => {
    isWatermarkEnabled.value = !isWatermarkEnabled.value
  }

  const enableWatermark = () => {
    isWatermarkEnabled.value = true
  }

  const disableWatermark = () => {
    isWatermarkEnabled.value = false
  }

  return {
    isWatermarkEnabled,
    toggleWatermark,
    enableWatermark,
    disableWatermark
  }
}