<template>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h3 class="text-white font-semibold mb-5">{{ title }}</h3>

    <div v-if="loading" class="h-40 flex items-center justify-center text-gray-500 text-sm">
      <div class="w-5 h-5 border-2 border-gray-700 border-t-brand-500 rounded-full animate-spin mr-2" />
    </div>

    <div v-else class="flex items-center gap-6">
      <div class="relative flex-shrink-0">
        <canvas ref="canvas" width="140" height="140" />
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="text-center">
            <p class="text-white font-bold text-lg">{{ total }}</p>
            <p class="text-gray-500 text-xs">Total</p>
          </div>
        </div>
      </div>

      <div class="flex-1 space-y-2">
        <div v-for="(item, i) in items" :key="i" class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full" :style="{ background: colors[i % colors.length] }" />
            <span class="text-gray-400 text-xs capitalize">{{ item.label }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-white text-xs font-semibold">{{ item.value }}</span>
            <span class="text-gray-600 text-xs">{{ total ? Math.round(item.value / total * 100) : 0 }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue'

const props = defineProps({
  title:   { type: String, default: 'Distribution' },
  items:   { type: Array,  default: () => [] },   // [{ label, value }]
  loading: { type: Boolean, default: false },
})

const canvas = ref(null)
const colors = ['#22c55e','#f59e0b','#3b82f6','#a855f7','#ef4444','#06b6d4','#f97316']
const total  = computed(() => props.items.reduce((s, i) => s + i.value, 0))

function draw() {
  const el = canvas.value
  if (!el || !props.items.length) return
  const ctx = el.getContext('2d')
  const cx = 70, cy = 70, r = 55, innerR = 35

  ctx.clearRect(0, 0, 140, 140)

  let startAngle = -Math.PI / 2
  const tot = total.value || 1

  props.items.forEach((item, i) => {
    const slice = (item.value / tot) * 2 * Math.PI
    ctx.beginPath()
    ctx.moveTo(cx, cy)
    ctx.arc(cx, cy, r, startAngle, startAngle + slice)
    ctx.closePath()
    ctx.fillStyle = colors[i % colors.length]
    ctx.fill()
    startAngle += slice
  })

  // Donut hole
  ctx.beginPath()
  ctx.arc(cx, cy, innerR, 0, 2 * Math.PI)
  ctx.fillStyle = '#111827'
  ctx.fill()
}

watch(() => props.items, async () => { await nextTick(); draw() }, { deep: true })
onMounted(async () => { await nextTick(); draw() })
</script>
