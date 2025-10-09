import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '../css/app.css'

// สร้างตัวแปรมาเก็บ constant ของ Vue
const app = createApp(App)

app.use(router)
app.mount('#app')

// createApp(App).use(store).use(router).mount('#app')
