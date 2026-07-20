<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-if="message" class="text-sm text-red-600 mt-1">
        {{ message }}
    </div>
</template>
