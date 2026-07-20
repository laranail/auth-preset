<script setup>
defineProps({
    for: String,
    value: String,
});
</script>

<template>
    <label :for="for" class="block text-sm font-medium text-gray-700">
        {{ value }}
    </label>
</template>
