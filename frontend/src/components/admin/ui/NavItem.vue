<template>
  <RouterLink
    :to="item.to"
    :class="[
      'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group',
      isActive
        ? 'bg-brand-600 text-white shadow-lg shadow-brand-900/40'
        : 'text-gray-400 hover:text-white hover:bg-gray-800'
    ]"
    :title="collapsed ? item.label : ''"
  >
    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
    <span v-if="!collapsed" class="whitespace-nowrap">{{ item.label }}</span>
    <span
      v-if="item.badge && !collapsed"
      class="ml-auto text-xs bg-red-500 text-white px-1.5 py-0.5 rounded-full font-bold"
    >{{ item.badge }}</span>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  item:      { type: Object, required: true },
  collapsed: { type: Boolean, default: false },
})

const route    = useRoute()
const isActive = computed(() =>
  props.item.exact
    ? route.path === props.item.to
    : route.path.startsWith(props.item.to)
)
</script>
