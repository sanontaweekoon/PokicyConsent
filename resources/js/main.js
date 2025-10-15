import { createApp } from 'vue'
import App from './App.vue'
import VueAwesomePaginate from 'vue-awesome-paginate'
import 'vue-awesome-paginate/dist/style.css'
import router from './router'
import '../css/app.css'
import { AuthService } from './services/AuthService'

AuthService.completeLoginFromCallback();

// สร้างตัวแปรมาเก็บ constant ของ Vue
const app = createApp(App)

app.use(router)
app.use(VueAwesomePaginate)
app.mount('#app')


// createApp(App).use(store).use(router).mount('#app')
