import { ref, watch, type Ref, onUnmounted } from 'vue'
import type { SignatureResult } from '@/composables/useSignaturePad'

export function useCanvasSignature(canvasRef: Ref<HTMLCanvasElement | null>) {
  const hasDrawn = ref(false)
  let ctx: CanvasRenderingContext2D | null = null
  let isDrawing = false
  let lastX = 0
  let lastY = 0

  // ── FIX 1: initialize ctx as soon as the canvas element is mounted ────────
  watch(canvasRef, (canvas) => {
    if (!canvas) { ctx = null; return }
    ctx = canvas.getContext('2d')
    if (!ctx) return
    ctx.strokeStyle = '#000000'
    ctx.lineWidth = 2.5
    ctx.lineCap = 'round'
    ctx.lineJoin = 'round'
  }, { immediate: true })

  const getCoordinates = (e: MouseEvent | TouchEvent) => {
    const canvas = canvasRef.value
    if (!canvas) return { x: 0, y: 0 }
    const rect = canvas.getBoundingClientRect()
    const scaleX = canvas.width / rect.width
    const scaleY = canvas.height / rect.height
    if (e instanceof MouseEvent) {
      return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY }
    }
    const touch = e.touches[0]
    return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY }
  }

  const startDrawing = (e: MouseEvent | TouchEvent) => {
    if (!ctx) return
    isDrawing = true
    const { x, y } = getCoordinates(e)
    ctx.beginPath()
    ctx.moveTo(x, y)
    lastX = x
    lastY = y
  }

  const draw = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing || !ctx) return
    const { x, y } = getCoordinates(e)
    ctx.beginPath()
    ctx.moveTo(lastX, lastY)
    ctx.lineTo(x, y)
    ctx.stroke()
    lastX = x
    lastY = y
    // ── FIX 2: only set hasDrawn after actual stroke pixels are drawn ────────
    if (!hasDrawn.value) hasDrawn.value = true
  }

  const stopDrawing = () => { isDrawing = false }

  const clear = () => {
    const canvas = canvasRef.value
    if (!canvas || !ctx) return
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    hasDrawn.value = false
  }

  const getBase64 = (): string => {
    const canvas = canvasRef.value
    if (!canvas || !hasDrawn.value) return ''
    return canvas.toDataURL('image/png').split(',')[1]
  }

  // ── FIX 3: return shape that matches SignatureResult exactly ─────────────
  const getResult = (): SignatureResult => ({
    imageData: getBase64(),
  })

  onUnmounted(() => { ctx = null })

  return {
    hasDrawn,
    clear,
    getBase64,
    getResult,
    handlers: {
      handleMouseDown: (e: MouseEvent) => startDrawing(e),
      handleMouseMove: (e: MouseEvent) => draw(e),
      handleMouseUp:   () => stopDrawing(),
      handleMouseLeave: () => stopDrawing(),
      handleTouchStart: (e: TouchEvent) => { e.preventDefault(); startDrawing(e) },
      handleTouchMove:  (e: TouchEvent) => { e.preventDefault(); draw(e) },
      handleTouchEnd:   () => stopDrawing(),
    },
  }
}