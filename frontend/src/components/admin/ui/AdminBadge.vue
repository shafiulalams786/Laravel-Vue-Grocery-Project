<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', classes]">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  color: { type: String, default: 'gray' },
})

const classes = computed(() => ({
  green:  'bg-brand-900/50 text-brand-400 border border-brand-800',
  red:    'bg-red-900/50 text-red-400 border border-red-800',
  yellow: 'bg-yellow-900/50 text-yellow-400 border border-yellow-800',
  blue:   'bg-blue-900/50 text-blue-400 border border-blue-800',
  orange: 'bg-orange-900/50 text-orange-400 border border-orange-800',
  purple: 'bg-purple-900/50 text-purple-400 border border-purple-800',
  gray:   'bg-gray-800 text-gray-400 border border-gray-700',
}[props.color]))
</script>
