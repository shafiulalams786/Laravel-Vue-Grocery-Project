<template>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h3 class="text-white font-semibold">Revenue Overview</h3>
        <p class="text-gray-500 text-xs mt-0.5">Daily sales for the last {{ period }} days</p>
      </div>
      <div class="flex gap-1">
        <button v-for="p in periods" :key="p"
          @click="selectedPeriod = p; $emit('period-change', p)"
          :class="['px-3 py-1.5 text-xs rounded-lg font-medium transition-colors',
            selectedPeriod === p ? 'bg-brand-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white']">
          {{ p }}d
        </button>
      </div>
    </div>

    <div v-if="loading" class="h-56 flex items-center justify-center text-gray-500 text-sm">
      <div class="w-5 h-5 border-2 border-gray-700 border-t-brand-500 rounded-full animate-spin mr-2" />
      Loading chart...
    </div>

    <div v-else class="relative h-56">
      <canvas ref="chartCanvas" class="w-full h-full" />
    </div>

    <!-- Summary row -->
    <div class="grid grid-cols-3 gap-4 mt-5 pt-4 border-t border-gray-800">
      <div>
        <p class="text-gray-500 text-xs">Total Revenue</p>
        <p class="text-white font-bold">${{ totalRevenue.toFixed(2) }}</p>
      </div>
      <div>
        <p class="text-gray-500 text-xs">Total Orders</p>
        <p class="text-white font-bold">{{ totalOrders }}</p>
      </div>
      <div>
        <p class="text-gray-500 text-xs">Avg / Day</p>
        <p class="text-white font-bold">${{ avgRevenue }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue'

const props = defineProps({
  data:    { type: Array,   default: () => [] },
  loading: { type: Boolean, default: false },
  period:  { type: Number,  default: 30 },
})
const emit = defineEmits(['period-change'])

const chartCanvas   = ref(null)
const selectedPeriod = ref(props.period)
const periods       = [7, 14, 30, 60]

const totalRevenue = computed(() => props.data.reduce((s, d) => s + d.revenue, 0))
const totalOrders  = computed(() => props.data.reduce((s, d) => s + d.orders,  0))
const avgRevenue   = computed(() => props.data.length ? (totalRevenue.value / props.data.length).toFixed(2) : '0.00')

function drawChart() {
  const canvas = chartCanvas.value
  if (!canvas || !props.data.length) return
  const ctx = canvas.getContext('2d')
  const dpr = window.devicePixelRatio || 1
  const rect = canvas.getBoundingClientRect()
  canvas.width  = rect.width  * dpr
  canvas.height = rect.height * dpr
  ctx.scale(dpr, dpr)
  const W = rect.width, H = rect.height
  const pad = { top: 10, right: 10, bottom: 30, left: 50 }
  const cw = W - pad.left - pad.right
  const ch = H - pad.top  - pad.bottom

  ctx.clearRect(0, 0, W, H)

  const revenues = props.data.map(d => d.revenue)
  const maxVal   = Math.max(...revenues, 1)
  const minVal   = 0

  // Grid lines
  const gridLines = 4
  ctx.strokeStyle = 'rgba(255,255,255,0.05)'
  ctx.lineWidth   = 1
  for (let i = 0; i <= gridLines; i++) {
    const y = pad.top + ch - (i / gridLines) * ch
    ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + cw, y); ctx.stroke()
    const label = '$' + ((maxVal * i / gridLines)).toFixed(0)
    ctx.fillStyle = '#6b7280'; ctx.font = '10px DM Sans'; ctx.textAlign = 'right'
    ctx.fillText(label, pad.left - 6, y + 4)
  }

  if (revenues.length < 2) return

  const xStep = cw / (revenues.length - 1)
  const points = revenues.map((v, i) => ({
    x: pad.left + i * xStep,
    y: pad.top + ch - ((v - minVal) / (maxVal - minVal)) * ch,
  }))

  // Gradient fill
  const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + ch)
  grad.addColorStop(0,   'rgba(34, 197, 94, 0.25)')
  grad.addColorStop(1,   'rgba(34, 197, 94, 0.0)')
  ctx.beginPath()
  ctx.moveTo(points[0].x, pad.top + ch)
  points.forEach(p => ctx.lineTo(p.x, p.y))
  ctx.lineTo(points[points.length - 1].x, pad.top + ch)
  ctx.closePath()
  ctx.fillStyle = grad
  ctx.fill()

  // Line
  ctx.beginPath()
  ctx.strokeStyle = '#22c55e'
  ctx.lineWidth   = 2
  ctx.lineJoin    = 'round'
  ctx.lineCap     = 'round'
  points.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y))
  ctx.stroke()

  // X-axis labels (every nth)
  const step = Math.ceil(props.data.length / 6)
  ctx.fillStyle  = '#6b7280'; ctx.font = '9px DM Sans'; ctx.textAlign = 'center'
  props.data.forEach((d, i) => {
    if (i % step === 0 || i === props.data.length - 1) {
      const label = d.date.slice(5) // MM-DD
      ctx.fillText(label, points[i].x, H - 8)
    }
  })

  // Dots on data points
  points.forEach(p => {
    ctx.beginPath()
    ctx.arc(p.x, p.y, 3, 0, Math.PI * 2)
    ctx.fillStyle   = '#22c55e'
    ctx.strokeStyle = '#111827'
    ctx.lineWidth   = 1.5
    ctx.fill(); ctx.stroke()
  })
}

watch(() => props.data, async () => { await nextTick(); drawChart() }, { deep: true })
onMounted(async () => { await nextTick(); drawChart() })
</script>
