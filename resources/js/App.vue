<template>
  <router-view />
</template>


<script>

let brand = " | PolicyConsent"
export default {
  name: 'App',
  watch: {
    $route(to){
      document.title = to.meta.title + brand
      // document.description = to.meta.description 
    }
  }
}
</script>