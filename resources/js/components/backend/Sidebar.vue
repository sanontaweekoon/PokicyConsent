<template>
  <!-- Desktop sidebar -->
  <aside class="z-20 flex-shrink-0 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block">
    <div class="py-4 text-gray-500 dark:text-gray-400">
      <a class="ml-6 text-sm font-bold text-zinc-500 dark:text-gray-200" href="#">
        การจัดทำและรับทราบ นโยบายองค์กร
      </a>
      
      <ul class="mt-6">
        <li class="relative px-6 py-3">
          <span class="absolute inset-y-0 left-0 w-1 bg-red-700 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
          <router-link to="/backend/policys"
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 512 512">
              <path fill="#A4262C"
                d="M256 152a88 88 0 1 0 0-176 88 88 0 1 0 0 176zm0 298.7l0-149.3c16.3-6.8 32.9-13.7 49.7-20.7 39-16.2 80.8-24.6 123.1-24.6l19.2 0 0 160-19.2 0c-59.1 0-117.7 11.7-172.3 34.5l-.5 .2zM256 232l-25.1-10.5C184.1 202 133.9 192 83.2 192L48 192c-26.5 0-48 21.5-48 48L0 432c0 26.5 21.5 48 48 48l35.2 0c50.7 0 100.9 10 147.7 29.5l12.8 5.3c7.9 3.3 16.7 3.3 24.6 0l12.8-5.3c46.8-19.5 97-29.5 147.7-29.5l35.2 0c26.5 0 48-21.5 48-48l0-192c0-26.5-21.5-48-48-48l-35.2 0c-50.7 0-100.9 10-147.7 29.5L256 232z" />
            </svg>
            <span class="ml-4">นโยบายองค์กร / มาตรการ</span>
          </router-link>
        </li>
      </ul>

      <ul>
        <li class="relative px-6 py-3">
          <router-link to="/backend"
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
            href="forms.html">
            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 576 512">
              <path fill="#A4262C"
                d="M64 128a112 112 0 1 1 224 0 112 112 0 1 1 -224 0zM0 464c0-97.2 78.8-176 176-176s176 78.8 176 176l0 6c0 23.2-18.8 42-42 42L42 512c-23.2 0-42-18.8-42-42l0-6zM432 64a96 96 0 1 1 0 192 96 96 0 1 1 0-192zm0 240c79.5 0 144 64.5 144 144l0 22.4c0 23-18.6 41.6-41.6 41.6l-144.8 0c6.6-12.5 10.4-26.8 10.4-42l0-6c0-51.5-17.4-98.9-46.5-136.7 22.6-14.7 49.6-23.3 78.5-23.3z" />
            </svg>
            <span class="ml-4">จัดการกลุ่มผู้รับนโยบาย</span>
          </router-link>
        </li>
      </ul>

      <ul>
        <li class="relative px-6 py-3">
          <router-link to="/backend"
            class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
            href="forms.html">
            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 384 512"><path fill="#A4262C" d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-277.5c0-17-6.7-33.3-18.7-45.3L258.7 18.7C246.7 6.7 230.5 0 213.5 0L64 0zM325.5 176L232 176c-13.3 0-24-10.7-24-24L208 58.5 325.5 176z"/></svg>
            <span class="ml-4">จัดการประเภทของนโยบาย</span>
          </router-link>
        </li>
      </ul>
    </div>
    
  </aside>

  <!-- Mobile sidebar -->
  <!-- Backdrop -->
  <!-- <div class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center md:hidden"></div> -->
  <aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-gray-100 dark:bg-gray-800 md:hidden">
    <div class="py-4 text-gray-500 dark:text-gray-400">
      <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#">Inventory</a>
      <ul class="mt-6">
        <li class="relative px-6 py-3">
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
            aria-hidden="true"></span>
          <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
            href="#">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"
              stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
              </path>
            </svg>
            <span class="ml-4">Dashboard</span>
          </a>
        </li>
      </ul>

    </div>
  </aside>
</template>

<script>
export default {
  methods: {

  }
};
</script>