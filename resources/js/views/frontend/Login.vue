<template>
  <div class="flex items-center justify-center w-screen min-h-screen px-4 bg-gray-50 dark:bg-gray-800 sm:px-6 lg:px-8">
    <div class="relative py-3 sm:max-w-md sm:mx-auto">
      <div class="px-20 py-6 mt-4 text-left shadow-lg bg-zinc-100 min-h-90 dark:bg-gray-900 rounded-xl">
        <div class="flex flex-col items-center justify-center h-full select-none">
          <div class="flex flex-col items-center justify-center mb-8">
            <img src="../../assets/img/logo-red.png" class="w-20" />

            <p class="m-0 text-[18px] font-semibold dark:text-white">ระบบจัดทำและรับทราบนโยบายองค์กร</p>
          </div>
        </div>
        <div className="mt-2">

          <button @click="LoginWithMicrosoft"
            class="flex w-full px-5 py-3 text-base font-semibold text-center text-white transition duration-200 ease-in bg-red-700 rounded-lg shadow-md cursor-pointer select-none hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2">
            <svg class="w-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
              <path fill="#ffffff"
                d="M0 93.7l183.6-25.3 0 177.4-183.6 0 0-152.1zM0 418.3l183.6 25.3 0-175.2-183.6 0 0 149.9zm203.8 28l244.2 33.7 0-211.6-244.2 0 0 177.9zm0-380.6l0 180.1 244.2 0 0-213.8-244.2 33.7z" />
            </svg>
            เข้าสู่ระบบด้วย Microsoft 365</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import http from "../../services/AuthService";

export default {
  mounted() {
    let token = this.$route?.query?.token

    if (!token) {
      const usp = new URLSearchParams(window.location.search)
      token = usp.get('token')
    }

    if (token) {
      localStorage.setItem('auth_token', token)
      http.defaults.headers.common['Authorization'] = `Bearer ${token}`
      this.$router.push({ name: 'Policys' })
    } else {
      localStorage.removeItem('auth_token', token)
      delete http.defaults.headers.common['Authorization']
    }
  },
  methods: {
    LoginWithMicrosoft() {
      window.location.href = '/login/microsoft'
    }
  }
};
</script>