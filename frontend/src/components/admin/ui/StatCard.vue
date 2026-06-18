<template>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-gray-700 transition-colors">
    <div class="flex items-start justify-between mb-4">
      <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', iconBg]">
        <component :is="icon" class="w-5 h-5" :class="iconColor" />
      </div>
      <div v-if="change !== undefined" :class="['flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-lg', change >= 0 ? 'bg-brand-900/40 text-brand-400' : 'bg-red-900/40 text-red-400']">
        <span>{{ change >= 0 ? '↑' : '↓' }}</span>
        <span>{{ Math.abs(change) }}%</span>
      </div>
    </div>
    <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mb-1">{{ label }}</p>
    <p class="text-white text-2xl font-bold">{{ value }}</p>
    <p v-if="sub" class="text-gray-500 text-xs mt-1">{{ sub }}</p>
  </div>
</template>

<script setup>
defineProps({
  label:   { type: String, required: true },
  value:   { type: [String, Number], required: true },
  sub:     { type: String, default: '' },
  icon:    { type: Object, required: true },
  iconBg:  { type: String, default: 'bg-brand-900/40' },
  iconColor: { type: String, default: 'text-brand-400' },
  change:  { type: Number, default: undefined },
})
</script>
