<script setup>
defineProps({
  text: {
    type: String,
    default: '',
  },
  bordered: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <header class="flex items-center mb-4 mr-[60px] max-w-full justify-center mx-auto">
    <figure class="shrink-0 mr-4">
      <img 
        src="/images/logo/logo.png" 
        class="w-10 h-auto" 
        alt="ALF Logo"
        width="96"
        height="96"
      >
    </figure>

    <div class="text-center">
      <h1 class="text-sm font-bold uppercase mb-1 tracking-widest">
        Arellano Law Foundation
      </h1>
      <address class="not-italic text-sm tracking-wide">
        <p>Taft Ave, Cor. Menlo St. Pasay City</p>
        <p>Tel. No. 404-3089 to 93</p>
      </address>
    </div>
  </header>

  <div :class="[
      'p-4',
      bordered ? 'border-t border-l border-r border-zinc-200' : ''
    ]"
  >
    <p v-if="text" class="font-semibold text-sm underline uppercase text-center tracking-widest">
      {{ text }}
    </p>
  </div>
</template>
