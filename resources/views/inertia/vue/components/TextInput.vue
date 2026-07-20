<script setup>
defineProps({
    value: String,
    modelValue: [String, Number, Boolean],
});

defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full"
    />
</template>
