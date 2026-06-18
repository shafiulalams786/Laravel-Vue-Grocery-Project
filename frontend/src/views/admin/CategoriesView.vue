<template>
  <div class="space-y-4 max-w-3xl">
    <div class="flex justify-end">
      <button @click="openCreate" class="flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700">
        <PlusIcon class="w-4 h-4" /> Add Category
      </button>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-500">
        <div class="w-5 h-5 border-2 border-gray-700 border-t-brand-500 rounded-full animate-spin mx-auto" />
      </div>
      <div v-else class="divide-y divide-gray-800">
        <div v-for="cat in categories" :key="cat.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-800/50 transition-colors">
          <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0">
            <img v-if="cat.image_url" :src="cat.image_url" class="w-10 h-10 rounded-xl object-cover" />
            <span v-else class="text-xl">{{ catEmojis[cat.slug] || '📦' }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-gray-200 font-medium text-sm">{{ cat.name }}</p>
            <p class="text-gray-500 text-xs">{{ cat.products_count }} products · Sort {{ cat.sort_order }}</p>
          </div>
          <AdminBadge :color="cat.is_active ? 'green' : 'gray'">{{ cat.is_active ? 'Active' : 'Inactive' }}</AdminBadge>
          <div class="flex gap-1">
            <button @click="openEdit(cat)" class="p-1.5 text-gray-500 hover:text-brand-400 hover:bg-gray-700 rounded-lg">
              <PencilIcon class="w-4 h-4" />
            </button>
            <button @click="deleteCategory(cat)" class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-gray-700 rounded-lg">
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <AdminModal v-model="showForm" :title="editing ? 'Edit Category' : 'New Category'" size="sm">
      <div class="space-y-4">
        <div>
          <label class="form-label">Name *</label>
          <input v-model="form.name" class="admin-input w-full" placeholder="Fresh Fruits" />
        </div>
        <div>
          <label class="form-label">Description</label>
          <textarea v-model="form.description" rows="2" class="admin-input w-full resize-none" />
        </div>
        <div>
          <label class="form-label">Sort Order</label>
          <input v-model="form.sort_order" type="number" class="admin-input w-full" />
        </div>
        <div>
          <label class="form-label">Image</label>
          <input type="file" @change="e => imageFile = e.target.files[0]" accept="image/*" class="admin-input w-full text-gray-400" />
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="rounded border-gray-600 bg-gray-800 text-brand-600" />
          <span class="text-gray-300 text-sm">Active</span>
        </label>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showForm = false" class="px-4 py-2 text-sm text-gray-400 bg-gray-800 rounded-xl">Cancel</button>
          <button @click="save" :disabled="saving" class="px-4 py-2 text-sm bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-50">
            {{ saving ? 'Saving…' : editing ? 'Save' : 'Create' }}
          </button>
        </div>
      </template>
    </AdminModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminCategoryAPI } from '@/services/adminApi'
import { useToast } from 'vue-toastification'
import AdminBadge from '@/components/admin/ui/AdminBadge.vue'
import AdminModal from '@/components/admin/ui/AdminModal.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const toast = useToast()
const categories = ref([]), loading = ref(true), saving = ref(false)
const showForm = ref(false), editing = ref(null), imageFile = ref(null)
const catEmojis = { 'fresh-fruits': '🍎', 'vegetables': '🥦', 'dairy-eggs': '🥛', 'meat-fish': '🥩', 'bakery': '🍞', 'beverages': '🧃', 'snacks': '🍪', 'pantry': '🫙' }
const defaultForm = () => ({ name: '', description: '', sort_order: 0, is_active: true })
const form = ref(defaultForm())

async function load() {
  loading.value = true
  const { data } = await adminCategoryAPI.list()
  categories.value = data; loading.value = false
}

function openCreate() { editing.value = null; form.value = defaultForm(); imageFile.value = null; showForm.value = true }
function openEdit(c)  { editing.value = c; form.value = { ...c }; imageFile.value = null; showForm.value = true }

async function save() {
  saving.value = true
  const fd = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (v === null) return
    fd.append(k, typeof v === 'boolean' ? (v ? '1' : '0') : v)
  })
  if (imageFile.value) fd.append('image', imageFile.value)
  try {
    if (editing.value) await adminCategoryAPI.update(editing.value.id, fd)
    else               await adminCategoryAPI.store(fd)
    toast.success('Category saved'); showForm.value = false; load()
  } catch (e) { toast.error(e.response?.data?.message || 'Failed') }
  saving.value = false
}

async function deleteCategory(c) {
  if (!confirm(`Delete "${c.name}"?`)) return
  try { await adminCategoryAPI.destroy(c.id); toast.success('Deleted'); load() }
  catch (e) { toast.error(e.response?.data?.message || 'Cannot delete') }
}

onMounted(load)
</script>

<style scoped>
.admin-input { @apply bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600; }
.form-label  { @apply block text-gray-400 text-xs font-medium mb-1.5 uppercase tracking-wide; }
</style>
