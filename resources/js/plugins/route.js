import { createWebHistory, createRouter } from "vue-router";
import DashboardLayout from '../layouts/dashboard-layout';
import AuthLayout from '../layouts/auth-layout';
import Register from '../components/auth/register';
import Login from '../components/auth/login';
import ForgetPassword from '../components/auth/forget-password';
import ResetPassword from '../components/auth/reset-password';
import EmailVerification from '../components/dashboard/email-verification';
import Profile from '../components/dashboard/profile';
import Home from '../components/web/home';
import Chat from '../components/web/chat';
import HelloTable from '../components/dashboard/hello/hello-table';
import ManyImageTable from '../components/dashboard/many-image/hello-table';
import AuthenticatedGuard from "../shared/guards/authenticated-guard";
import GuestGuard from "../shared/guards/guest-guard";
import PageNotFound from "../shared/components/page-not-found";
import ExamManaging from "../components/dashboard/exam-managing/exam-managing-table.vue";
import ExamUser from "../components/dashboard/exam-solution/users.vue";
import ExamShow from "../components/dashboard/exam-solution/exam-show.vue";
const routes = [
  {
    path: "",
    redirect: "/home"
  },
  {
    path: "/admin",
    component: DashboardLayout,
    beforeEnter: [
      // AuthenticatedGuard
    ],
    children: [
      { path: "verify-email", component: EmailVerification },
      { path: "profile", component: Profile },
      { path: "hellos", component: HelloTable },
      { path: "many-image", component: ManyImageTable },
    ]
  },
  {
    path: "",
    component: AuthLayout,
    beforeEnter: [
      GuestGuard
    ],
    children: [
      { path: "register", component: Register },
      { path: "login", component: Login },
      { path: "forget-password", component: ForgetPassword },
      { path: "reset-password/:token", component: ResetPassword },
    ]
  },
  {
    path: "",
    component: DashboardLayout,
    beforeEnter: [
      AuthenticatedGuard
    ],
    children: [
      { path: "home", component: Home, name: "home" },
      { path: "exam-managing", component: ExamManaging, name: 'exam-managing' },
      { path: "exam-solutions", component: ExamUser, name: 'exam-solutions' },
      { path: "user-exam/:userId", component: ExamShow, name: 'user-exam' },
      { path: "chat", component: Chat, beforeEnter: [AuthenticatedGuard] },
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    component: PageNotFound
  }
];
const router = createRouter({
  history: createWebHistory(),
  routes,
});
export default router;