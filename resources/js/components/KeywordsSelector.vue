<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useKeywords } from '@/composables/useKeywords';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ChevronDown, Plus, X } from 'lucide-vue-next';

const props = defineProps<{
  modelValue: string[];
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string[]];
}>();

const { keywords, fetchKeywords, createKeyword } = useKeywords();

const selected = computed({
  get: () => props.modelValue,
  set: (value: string[]) => emit('update:modelValue', value),
});

const search = ref('');
const isOpen = ref(false);

onMounted(async () => {
  await fetchKeywords();
});

const filteredKeywords = computed(() => {
  if (!search.value) return keywords.value;
  return keywords.value.filter(k => k.name.toLowerCase().includes(search.value.toLowerCase()));
});

const addKeyword = (name: string) => {
  if (!selected.value.includes(name)) {
    selected.value = [...selected.value, name];
  }
  search.value = '';
  isOpen.value = false;
};

const removeKeyword = (name: string) => {
  selected.value = selected.value.filter(k => k !== name);
};

const createAndAdd = async () => {
  if (!search.value.trim()) return;
  try {
    const newKeyword = await createKeyword(search.value.trim());
    addKeyword(newKeyword.name);
  } catch (err) {
    console.error('Failed to create keyword:', err);
  }
};

const handleInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  search.value = target.value;
};
</script>

<template>
  <div class="space-y-2">
    <Label>Keywords</Label>
    <div class="relative">
      <div class="flex flex-wrap gap-2 mb-2">
        <span
          v-for="kw in selected"
          :key="kw"
          class="px-3 py-1 bg-primary/10 text-primary text-xs font-medium rounded-full flex items-center gap-1"
        >
          {{ kw }}
          <button @click="removeKeyword(kw)" class="hover:text-destructive">
            <X class="w-3 h-3" />
          </button>
        </span>
      </div>
      <DropdownMenu v-model:open="isOpen">
        <DropdownMenuTrigger as-child>
          <Button variant="outline" class="w-full justify-between">
            <span v-if="search" class="text-muted-foreground">{{ search }}</span>
            <span v-else class="text-muted-foreground">Select or type to create...</span>
            <ChevronDown class="w-4 h-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-full">
          <div class="p-2">
            <Input
              v-model="search"
              placeholder="Search or type new keyword..."
              @input="handleInput"
              @keydown.enter="createAndAdd"
            />
          </div>
          <div class="max-h-40 overflow-y-auto">
            <DropdownMenuItem
              v-for="kw in filteredKeywords"
              :key="kw.id"
              @click="addKeyword(kw.name)"
              :disabled="selected.includes(kw.name)"
            >
              {{ kw.name }}
            </DropdownMenuItem>
          </div>
          <div v-if="search && !filteredKeywords.some(k => k.name.toLowerCase() === search.toLowerCase())" class="p-2 border-t">
            <Button @click="createAndAdd" size="sm" class="w-full">
              <Plus class="w-4 h-4 mr-2" />
              Create "{{ search }}"
            </Button>
          </div>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  </div>
</template>
