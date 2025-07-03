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
  textSize: {
    type: String,
    default: 'sm', // sm, xs, or custom Tailwind class
    validator: (value) => ['xs', 'sm', 'base', 'lg', 'xl', '2xl'].includes(value),
  },
  subtextSize: {
    type: String,
    default: 'sm', // sm, xs, or custom Tailwind class
    validator: (value) => ['xs', 'sm', 'base', 'lg', 'xl', '2xl'].includes(value),
  },
  marginBottom: {
    type: String,
    default: '4', // Tailwind mb- class (1-8)
    
  },
})
</script>

<template>
  <header :class="`flex items-center mb-${marginBottom} mr-[60px] max-w-full justify-center mx-auto`">
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
      <h1 :class="`text-${textSize} font-bold uppercase mb-1 tracking-widest`">
        Arellano Law Foundation
      </h1>
      <address class="not-italic" :class="`text-${textSize} tracking-wide`">
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
    <p 
      v-if="text" 
      :class="`font-semibold text-${subtextSize} underline uppercase text-center tracking-widest`"
    >
      {{ text }}
    </p>
  </div>
</template>