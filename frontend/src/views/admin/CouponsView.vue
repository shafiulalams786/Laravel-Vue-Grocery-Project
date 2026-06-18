<template>
  <div class="space-y-4 max-w-4xl">
    <div class="flex justify-end">
      <button @click="openCreate" class="flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700">
        <PlusIcon class="w-4 h-4" /> Create Coupon
      </button>
    </div>

    <AdminTable :columns="columns" :rows="coupons" :loading="loading">
      <template #default="{ row }">
        <td class="px-4 py-3">
          <span class="font-mono text-brand-400 font-bold text-sm bg-brand-900/30 px-2 py-0.5 rounded-lg">{{ row.code }}</span>
        </td>
        <td class="px-4 py-3">
          <AdminBadge :color="row.type === 'percent' ? 'blue' : row.type === 'flat' ? 'purple' : 'green'">
            {{ row.type === 'percent' ? row.value + '% Off' : row.type === 'flat' ? '$' + row.value + ' Off' : 'Free Delivery' }}
          </AdminBadge>
        </td>
        <td class="px-4 py-3 text-gray-400 text-sm">${{ row.min_order || 0 }}</td>
        <td class="px-4 py-3 text-gray-300 text-sm">
          {{ row.times_used }}{{ row.max_uses ? ' / ' + row.max_uses : '' }}
        </td>
        <td class="px-4 py-3 text-gray-400 text-xs">{{ row.expires_at ? fmtDate(row.expires_at) : 'Never' }}</td>
        <td class="px-4 py-3">
          <AdminBadge :color="isValid(row) ? 'green' : 'red'">{{ isValid(row) ? 'Active' : 'Inactive' }}</AdminBadge>
        </td>
        <td class="px-4 py-3">
          <div class="flex gap-1">
            <button @click="openEdit(row)" class="p-1.5 text-gray-500 hover:text-brand-400 hover:bg-gray-700 rounded-lg"><PencilIcon class="w-4 h-4" /></button>
            <button @click="deleteCoupon(row)" class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-gray-700 rounded-lg"><TrashIcon class="w-4 h-4" /></button>
          </div>
        </td>
      </template>
    </AdminTable>

    <!-- Form Modal -->
    <AdminModal v-model="showForm" :title="editing ? 'Edit Coupon' : 'New Coupon'" size="md">
      <div class="space-y-4">
        <div class="flex gap-3">
          <div class="flex-1">
            <label class="form-label">Coupon Code *</label>
            <input v-model="form.code" class="admin-input w-full uppercase" placeholder="SAVE20" />
          </div>
          <div class="pt-5">
            <button @click="generateCode" class="px-3 py-2 text-xs bg-gray-700 text-gray-300 rounded-xl hover:bg-gray-600">Auto-generate</button>
          </div>
        </div>
        <div>
          <label class="form-label">Discount Type *</label>
          <select v-model="form.type" class="admin-select w-full">
            <option value="percent">Percentage Off</option>
            <option value="flat">Flat Amount Off</option>
            <option value="free_delivery">Free Delivery</option>
          </select>
        </div>
        <div v-if="form.type !== 'free_delivery'">
          <label class="form-label">Value *</label>
          <input v-model="form.value" type="number" step="0.01" class="admin-input w-full"
            :placeholder="form.type === 'percent' ? '20 (= 20%)' : '5.00 (= $5 off)'" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Min Order Amount</label>
            <input v-model="form.min_order" type="number" step="0.01" class="admin-input w-full" placeholder="0.00" />
          </div>
          <div>
            <label class="form-label">Max Uses</label>
            <input v-model="form.max_uses" type="number" class="admin-input w-full" placeholder="Unlimited" />
          </div>
        </div>
        <div>
          <label class="form-label">Expiry Date</label>
          <input v-model="form.expires_at" type="date" class="admin-input w-full" />
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
import { adminCouponAPI } from '@/services/adminApi'
import { useToast } from 'vue-toastification'
import AdminTable from '@/components/admin/ui/AdminTable.vue'
import AdminBadge from '@/components/admin/ui/AdminBadge.vue'
import AdminModal from '@/components/admin/ui/AdminModal.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const toast = useToast()
const coupons = ref([]), loading = ref(true), saving = ref(false)
const showForm = ref(false), editing = ref(null)
const defaultForm = () => ({ code: '', type: 'percent', value: '', min_order: '', max_uses: '', expires_at: '', is_active: true })
const form = ref(defaultForm())

const columns = [
  { key: 'code', label: 'Code' },
  { key: 'type', label: 'Discount' },
  { key: 'min', label: 'Min Order' },
  { key: 'uses', label: 'Uses' },
  { key: 'exp', label: 'Expires' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '' },
]

const fmtDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
const isValid = (c) => c.is_active && (!c.expires_at || new Date(c.expires_at) > new Date()) && (!c.max_uses || c.times_used < c.max_uses)

async function load() {
  loading.value = true
  const { data } = await adminCouponAPI.list()
  coupons.value = data.data ?? data; loading.value = false
}

function openCreate() { editing.value = null; form.value = defaultForm(); showForm.value = true }
function openEdit(c)  { editing.value = c; form.value = { ...c, expires_at: c.expires_at?.slice(0, 10) ?? '' }; showForm.value = true }

async function generateCode() {
  const { data } = await adminCouponAPI.generateCode()
  form.value.code = data.code
}

async function save() {
  saving.value = true
  try {
    if (editing.value) await adminCouponAPI.update(editing.value.id, form.value)
    else               await adminCouponAPI.store(form.value)
    toast.success('Coupon saved'); showForm.value = false; load()
  } catch (e) { toast.error(e.response?.data?.message || 'Save failed') }
  saving.value = false
}

async function deleteCoupon(c) {
  if (!confirm(`Delete coupon "${c.code}"?`)) return
  await adminCouponAPI.destroy(c.id); toast.success('Deleted'); load()
}

onMounted(load)
</script>

<style scoped>
.admin-select { @apply bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500; }
.admin-input  { @apply bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600; }
.form-label   { @apply block text-gray-400 text-xs font-medium mb-1.5 uppercase tracking-wide; }
</style>
