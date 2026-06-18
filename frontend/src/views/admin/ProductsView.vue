<template>
  <div class="space-y-4">

    <!-- Toolbar -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3 items-center">
      <input v-model="filters.search" @input="debouncedFetch" type="text"
        placeholder="Search products…"
        class="admin-input w-56" />

      <select v-model="filters.category_id" @change="fetchProducts" class="admin-select">
        <option value="">All Categories</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>

      <select v-model="filters.status" @change="fetchProducts" class="admin-select">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>

      <select v-model="filters.stock" @change="fetchProducts" class="admin-select">
        <option value="">All Stock</option>
        <option value="low">Low Stock (≤10)</option>
        <option value="out">Out of Stock</option>
      </select>

      <div class="ml-auto flex gap-2">
        <div v-if="selected.length" class="flex items-center gap-2">
          <span class="text-gray-400 text-xs">{{ selected.length }} selected</span>
          <select v-model="bulkAction" class="admin-select text-xs">
            <option value="">Bulk Action</option>
            <option value="activate">Activate</option>
            <option value="deactivate">Deactivate</option>
            <option value="feature">Set Featured</option>
            <option value="unfeature">Remove Featured</option>
            <option value="delete">Delete</option>
          </select>
          <button @click="applyBulk" :disabled="!bulkAction" class="px-3 py-2 text-xs bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-40">Apply</button>
        </div>
        <button @click="openCreate" class="flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition-colors">
          <PlusIcon class="w-4 h-4" /> Add Product
        </button>
      </div>
    </div>

    <!-- Table -->
    <AdminTable
      :columns="columns" :rows="products" :loading="loading"
      selectable :selected-ids="selected" :all-selected="allSelected"
      :current-page="page" :total-pages="lastPage"
      @select-all="toggleAll" @select-row="toggleRow" @page="p => { page = p; fetchProducts() }"
    >
      <template #default="{ row }">
        <td class="px-4 py-3">
          <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center text-lg flex-shrink-0">
            {{ row.image_url ? '' : '🛒' }}
            <img v-if="row.image_url" :src="row.image_url" class="w-10 h-10 object-cover rounded-xl" />
          </div>
        </td>
        <td class="px-4 py-3">
          <p class="text-gray-200 text-sm font-medium">{{ row.name }}</p>
          <p class="text-gray-500 text-xs">{{ row.category?.name }}</p>
        </td>
        <td class="px-4 py-3 text-white font-semibold">
          ${{ row.sale_price ?? row.price }}
          <span v-if="row.sale_price" class="text-gray-600 text-xs line-through ml-1">${{ row.price }}</span>
        </td>
        <td class="px-4 py-3">
          <span :class="['text-sm font-semibold', row.stock === 0 ? 'text-red-400' : row.stock <= 10 ? 'text-yellow-400' : 'text-brand-400']">
            {{ row.stock }}
          </span>
          <span class="text-gray-500 text-xs ml-1">/ {{ row.unit }}</span>
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1.5">
            <AdminBadge :color="row.is_active ? 'green' : 'gray'">{{ row.is_active ? 'Active' : 'Inactive' }}</AdminBadge>
            <AdminBadge v-if="row.is_featured" color="orange">Featured</AdminBadge>
          </div>
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1">
            <button @click="openEdit(row)" class="p-1.5 text-gray-500 hover:text-brand-400 hover:bg-gray-700 rounded-lg">
              <PencilIcon class="w-4 h-4" />
            </button>
            <button @click="openStockModal(row)" class="p-1.5 text-gray-500 hover:text-blue-400 hover:bg-gray-700 rounded-lg" title="Adjust Stock">
              <AdjustmentsHorizontalIcon class="w-4 h-4" />
            </button>
            <button @click="deleteProduct(row)" class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-gray-700 rounded-lg">
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </td>
      </template>
    </AdminTable>

    <!-- Product Form Modal -->
    <AdminModal v-model="showForm" :title="editingProduct ? 'Edit Product' : 'Add Product'" size="lg">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="form-label">Product Name *</label>
          <input v-model="form.name" class="admin-input w-full" placeholder="Organic Apples" />
        </div>
        <div>
          <label class="form-label">Category *</label>
          <select v-model="form.category_id" class="admin-select w-full">
            <option value="">Select category</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="form-label">Unit *</label>
          <input v-model="form.unit" class="admin-input w-full" placeholder="lb, kg, each, bunch…" />
        </div>
        <div>
          <label class="form-label">Price *</label>
          <input v-model="form.price" type="number" step="0.01" class="admin-input w-full" placeholder="0.00" />
        </div>
        <div>
          <label class="form-label">Sale Price</label>
          <input v-model="form.sale_price" type="number" step="0.01" class="admin-input w-full" placeholder="0.00 (optional)" />
        </div>
        <div>
          <label class="form-label">Stock *</label>
          <input v-model="form.stock" type="number" class="admin-input w-full" placeholder="0" />
        </div>
        <div>
          <label class="form-label">Origin</label>
          <input v-model="form.origin" class="admin-input w-full" placeholder="USA, Mexico…" />
        </div>
        <div class="sm:col-span-2">
          <label class="form-label">Description</label>
          <textarea v-model="form.description" rows="3" class="admin-input w-full resize-none" placeholder="Product description…" />
        </div>
        <div>
          <label class="form-label">Product Image</label>
          <input type="file" @change="onImageChange" accept="image/*" class="admin-input w-full text-gray-400" />
        </div>
        <div class="flex items-center gap-6 pt-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-600 bg-gray-800 text-brand-600" />
            <span class="text-gray-300 text-sm">Active</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.is_featured" class="rounded border-gray-600 bg-gray-800 text-brand-600" />
            <span class="text-gray-300 text-sm">Featured</span>
          </label>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showForm = false" class="px-4 py-2 text-sm text-gray-400 bg-gray-800 rounded-xl hover:text-white">Cancel</button>
          <button @click="saveProduct" :disabled="saving" class="px-5 py-2 text-sm bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-50">
            {{ saving ? 'Saving…' : editingProduct ? 'Save Changes' : 'Create Product' }}
          </button>
        </div>
      </template>
    </AdminModal>

    <!-- Stock Adjust Modal -->
    <AdminModal v-model="showStock" title="Adjust Stock" size="sm">
      <div class="space-y-3">
        <p class="text-gray-400 text-sm">Product: <span class="text-white">{{ stockProduct?.name }}</span></p>
        <p class="text-gray-400 text-sm">Current stock: <span class="text-white font-bold">{{ stockProduct?.stock }}</span></p>
        <label class="form-label">New Stock Level</label>
        <input v-model="newStock" type="number" min="0" class="admin-input w-full" />
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showStock = false" class="px-4 py-2 text-sm text-gray-400 bg-gray-800 rounded-xl">Cancel</button>
          <button @click="saveStock" :disabled="saving" class="px-4 py-2 text-sm bg-brand-600 text-white rounded-xl hover:bg-brand-700">
            {{ saving ? 'Saving…' : 'Update Stock' }}
          </button>
        </div>
      </template>
    </AdminModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminProductAPI, adminCategoryAPI } from '@/services/adminApi'
import { useToast } from 'vue-toastification'
import AdminTable from '@/components/admin/ui/AdminTable.vue'
import AdminBadge from '@/components/admin/ui/AdminBadge.vue'
import AdminModal from '@/components/admin/ui/AdminModal.vue'
import { PlusIcon, PencilIcon, TrashIcon, AdjustmentsHorizontalIcon } from '@heroicons/vue/24/outline'

const toast = useToast()
const products = ref([]), categories = ref([])
const loading = ref(true), saving = ref(false)
const page = ref(1), lastPage = ref(1)
const selected = ref([]), bulkAction = ref('')
const showForm = ref(false), showStock = ref(false)
const editingProduct = ref(null), stockProduct = ref(null)
const newStock = ref(0), imageFile = ref(null)

const filters = ref({ search: '', category_id: '', status: '', stock: '' })

const defaultForm = () => ({ name: '', category_id: '', unit: '', price: '', sale_price: '', stock: 0, description: '', origin: '', is_active: true, is_featured: false })
const form = ref(defaultForm())

const columns = [
  { key: 'img', label: '', width: '60px' },
  { key: 'name', label: 'Product' },
  { key: 'price', label: 'Price' },
  { key: 'stock', label: 'Stock' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '' },
]

const allSelected = computed(() => products.value.length > 0 && products.value.every(p => selected.value.includes(p.id)))

async function fetchProducts() {
  loading.value = true
  const { data } = await adminProductAPI.list({ ...filters.value, page: page.value, per_page: 20 })
  products.value = data.data; lastPage.value = data.last_page
  selected.value = []; loading.value = false
}

let timer = null
function debouncedFetch() { clearTimeout(timer); timer = setTimeout(() => { page.value = 1; fetchProducts() }, 400) }

function toggleAll(v)       { selected.value = v ? products.value.map(p => p.id) : [] }
function toggleRow(id, v)   { if (v) selected.value.push(id); else selected.value = selected.value.filter(i => i !== id) }

function openCreate() { editingProduct.value = null; form.value = defaultForm(); imageFile.value = null; showForm.value = true }
function openEdit(p)  { editingProduct.value = p; form.value = { ...p, category_id: p.category_id, sale_price: p.sale_price ?? '' }; imageFile.value = null; showForm.value = true }
function openStockModal(p) { stockProduct.value = p; newStock.value = p.stock; showStock.value = true }

function onImageChange(e) { imageFile.value = e.target.files[0] }

async function saveProduct() {
  saving.value = true
  const fd = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (v === null || v === '') return
    fd.append(k, typeof v === 'boolean' ? (v ? '1' : '0') : v)
  })
  if (imageFile.value) fd.append('image', imageFile.value)
  try {
    if (editingProduct.value) await adminProductAPI.update(editingProduct.value.id, fd)
    else                       await adminProductAPI.store(fd)
    toast.success(editingProduct.value ? 'Product updated' : 'Product created')
    showForm.value = false; fetchProducts()
  } catch (e) { toast.error(e.response?.data?.message || 'Save failed') }
  saving.value = false
}

async function saveStock() {
  saving.value = true
  await adminProductAPI.adjustStock(stockProduct.value.id, newStock.value)
  toast.success('Stock updated'); showStock.value = false; fetchProducts()
  saving.value = false
}

async function applyBulk() {
  if (!bulkAction.value || !selected.value.length) return
  if (bulkAction.value === 'delete' && !confirm(`Delete ${selected.value.length} products?`)) return
  await adminProductAPI.bulk(selected.value, bulkAction.value)
  toast.success('Bulk action applied'); bulkAction.value = ''; fetchProducts()
}

async function deleteProduct(p) {
  if (!confirm(`Delete "${p.name}"?`)) return
  await adminProductAPI.destroy(p.id)
  toast.success('Product deleted'); fetchProducts()
}

onMounted(async () => {
  const [, cats] = await Promise.all([fetchProducts(), adminCategoryAPI.list()])
  categories.value = cats.data
})
</script>

<style scoped>
.admin-select { @apply bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500; }
.admin-input  { @apply bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600; }
.form-label   { @apply block text-gray-400 text-xs font-medium mb-1.5 uppercase tracking-wide; }
</style>
