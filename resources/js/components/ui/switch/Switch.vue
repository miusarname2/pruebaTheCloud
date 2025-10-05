<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = withDefaults(defineProps<{
  defaultChecked?: boolean
  checked?: boolean
  disabled?: boolean
  required?: boolean
  name?: string
  value?: string
  id?: string
  asChild?: boolean
  class?: string
}>(), {
  defaultChecked: false,
  disabled: false,
  required: false,
  asChild: false,
})

const emits = defineEmits<{
  'update:checked': [checked: boolean]
  'checkedChange': [checked: boolean]
}>()

const checked = computed({
  get() {
    return props.checked ?? props.defaultChecked
  },
  set(value) {
    emits('update:checked', value)
    emits('checkedChange', value)
  },
})
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="checked"
    :data-state="checked ? 'checked' : 'unchecked'"
    :disabled="disabled"
    :class="cn(
      'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=unchecked]:bg-input',
      props.class
    )"
    @click="checked = !checked"
  >
    <span
      :data-state="checked ? 'checked' : 'unchecked'"
      class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"
    />
  </button>
</template>
