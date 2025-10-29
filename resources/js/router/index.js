import {
    createRouter,
    createWebHistory
} from 'vue-router'


// import Layouts
import FrontendLayout from "../layouts/Auth.vue"
import BackendLayout from '../layouts/Backend.vue'

// Frontend
import Login from '../views/frontend/Login.vue'

// Backend
import PoliciesList from '../views/backend/PoliciesList.vue'
import Companies from '../views/backend/Companies.vue';
import PolicyCategory from '../views/backend/PolicyCategory.vue';
import RecipientGroups from '../views/backend/RecipientGroups.vue';

//Public
import PublicAck from '../views/public/PublicAck.vue'

const PoliciesForm = () => import('../views/backend/PoliciesForm.vue')

const routes = [{
        path: '/login',
        component: FrontendLayout,
        children: [{
            name: 'Login',
            path: '',
            component: Login
        }],
    },
    {
        path: '/backend',
        component: BackendLayout,
        meta: {
            requiresAuth: true
        },
        children: [{
                path: '',
                redirect: {
                    name: 'Policies'
                }
            },
            {
                path: 'policies',
                name: 'Policies',
                component: PoliciesList,
                meta: {
                    title: 'นโยบายองค์กร / มาตรการ'
                },
            },

            {
                path: 'policies/create',
                name: 'PolicyCreate',
                component: PoliciesForm,
                meta: {
                    title: 'สร้างข้อมูลนโยบาย/มาตรการองค์กร'
                },
            },

            {
                path: 'policies/:id/edit',
                name: 'PolicyEdit',
                component: PoliciesForm,
                props: true,
                meta: {
                    title: (r) => `แก้ไขนโยบาย #${r.params.id}`
                },
            },
            {
                path: 'recipient-groups',
                name: 'RecipientGroups',
                component: RecipientGroups,
                meta: {
                    title: 'จัดการกลุ่มผู้รับนโยบาย'
                }
            },
            {
                path: 'policy-category',
                name: 'Policy-PolicyCategory',
                component: PolicyCategory,
                meta: {
                    title: 'จัดการประเภทของนโยบาย'
                }
            },
        ],
    },

    {
        path: '/ack/:window',
        name: 'PublicAck',
        component: PublicAck,
        meta: {
            title: 'รับทราบนโยบายและมาตรการองค์กร',
        }
    },

    {
        path: ''
    },

    {
        path: '/',
        redirect: '/backend'
    }

]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes
})

router.afterEach((to) => {
    const defaultTitle = 'ระบบจัดการนโยบายและมาตรการองค์กร'
    const metaTitle = to.meta?.title
    let title = defaultTitle

    if (typeof metaTitle === 'function') {
        try {
            // เรียกฟังก์ชันด้วยพารามิเตอร์ route ปัจจุบัน
            title = metaTitle(to)
        } catch (e) {
            console.warn('Error evaluating meta.title', e)
            title = defaultTitle
        }
    } else if (typeof metaTitle === 'string') {
        title = metaTitle
    }
    // console.log('document.title:', title)
    document.title = title
})

router.beforeEach(async (to, from, next) => {
    const needAuth = to.matched.some(r => r.meta?.requiresAuth)
    if (!needAuth) return next()

    const hasToken = !!localStorage.getItem('auth_token')

    if (!hasToken) {
        return next({
            name: 'Login',
            query: {
                redirect: to.fullPath
            }
        })
    }

    return next()
})

export default router
