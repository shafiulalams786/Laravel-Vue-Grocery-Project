<template>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <!-- Table toolbar slot -->
    <div v-if="$slots.toolbar" class="px-5 py-4 border-b border-gray-800">
      <slot name="toolbar" />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-800">
            <th v-if="selectable" class="w-10 px-4 py-3 text-left">
              <input
                type="checkbox"
                class="rounded border-gray-600 bg-gray-800 text-brand-600"
                :checked="allSelected"
                @change="$emit('select-all', $event.target.checked)"
              />
            </th>
            <th
              v-for="col in columns" :key="col.key"
              class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap"
              :style="col.width ? { width: col.width } : {}"
            >{{ col.label }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          <tr v-if="loading">
            <td :colspan="columns.length + (selectable ? 1 : 0)" class="px-4 py-12 text-center">
              <div class="flex items-center justify-center gap-2 text-gray-500">
                <div class="w-5 h-5 border-2 border-gray-700 border-t-brand-500 rounded-full animate-spin" />
                Loading...
              </div>
            </td>
          </tr>
          <tr v-else-if="!rows.length">
            <td :colspan="columns.length + (selectable ? 1 : 0)" class="px-4 py-12 text-center text-gray-500">
              <slot name="empty">No records found</slot>
            </td>
          </tr>
          <template v-else>
            <tr
              v-for="row in rows" :key="row.id"
              class="hover:bg-gray-800/50 transition-colors"
              :class="{ 'bg-gray-800/30': selectedIds.includes(row.id) }"
            >
              <td v-if="selectable" class="w-10 px-4 py-3">
                <input
                  type="checkbox"
                  class="rounded border-gray-600 bg-gray-800 text-brand-600"
                  :checked="selectedIds.includes(row.id)"
                  @change="$emit('select-row', row.id, $event.target.checked)"
                />
              </td>
              <slot :row="row" />
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-800">
      <p class="text-xs text-gray-500">Page {{ currentPage }} of {{ totalPages }}</p>
      <div class="flex gap-2">
        <button @click="$emit('page', currentPage - 1)" :disabled="currentPage === 1"
          class="px-3 py-1.5 text-xs bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 disabled:opacity-40 transition-colors">
          ← Prev
        </button>
        <button @click="$emit('page', currentPage + 1)" :disabled="currentPage === totalPages"
          class="px-3 py-1.5 text-xs bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 disabled:opacity-40 transition-colors">
          Next →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns:      { type: Array,   required: true },
  rows:         { type: Array,   default: () => [] },
  loading:      { type: Boolean, default: false },
  selectable:   { type: Boolean, default: false },
  selectedIds:  { type: Array,   default: () => [] },
  allSelected:  { type: Boolean, default: false },
  currentPage:  { type: Number,  default: 1 },
  totalPages:   { type: Number,  default: 1 },
})
defineEmits(['select-all', 'select-row', 'page'])
</script>
