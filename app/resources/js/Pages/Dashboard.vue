<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const hosts = ref([]);
const availablePorts = ref([]);
const loading = ref(false);
const creating = ref(false);
const toggling = ref(null);
const deleting = ref(null);
const nginxStatus = ref('unknown');
const nginxLoading = ref(false);

const form = ref({
    domain: '',
    port: null,
});

const errors = ref(null);

const fetchHosts = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/hosts');
        hosts.value = data.data;
        availablePorts.value = data.available_ports;

        if (availablePorts.value.length > 0 && !form.value.port) {
            form.value.port = availablePorts.value[0];
        }
    } finally {
        loading.value = false;
    }
};

const createHost = async () => {
    creating.value = true;
    errors.value = null;

    try {
        const { data } = await axios.post('/api/hosts', form.value);
        hosts.value.unshift(data.data);

        await fetchHosts();

        form.value = {
            domain: '',
            port: availablePorts.value[0] || null
        };
    } catch (error) {
        errors.value = error.response?.data?.errors ?? { general: ['Unexpected error'] };
    } finally {
        creating.value = false;
    }
};

const toggleHost = async (host) => {
    toggling.value = host.id;
    try {
        const { data } = await axios.patch(`/api/hosts/${host.id}/status`, {
            active: host.status !== 'active',
        });
        hosts.value = hosts.value.map((h) => (h.id === host.id ? data.data : h));
    } finally {
        toggling.value = null;
    }
};

const deleteHost = async (host) => {
    if (!confirm(`Видалити ${host.domain}?`)) return;

    deleting.value = host.id;
    try {
        await axios.delete(`/api/hosts/${host.id}`);

        await fetchHosts();
    } finally {
        deleting.value = null;
    }
};

const fetchNginxStatus = async () => {
    try {
        const { data } = await axios.get('/api/nginx/status');
        nginxStatus.value = data.status;
    } catch (error) {
        nginxStatus.value = 'error';
    }
};

const nginxAction = async (action, message) => {
    nginxLoading.value = true;
    try {
        await axios.post(`/api/nginx/${action}`);
        alert(message);
        await fetchNginxStatus();
    } catch (error) {
        alert(error.response?.data?.message || 'Помилка виконання операції');
    } finally {
        nginxLoading.value = false;
    }
};

const startNginx = () => nginxAction('start', 'Nginx запущено');
const stopNginx = () => nginxAction('stop', 'Nginx зупинено');
const restartNginx = () => nginxAction('restart', 'Nginx перезапущено');
const reloadNginx = () => nginxAction('reload', 'Конфігурацію перезавантажено');

onMounted(() => {
    fetchHosts();
    fetchNginxStatus();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="text-2xl font-semibold text-gray-900">Virtual Hosts</h2>
                <p class="text-sm text-gray-500">
                    Панель керування
                </p>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-8">
                <!-- Nginx Control Panel -->
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Управління Nginx</h3>
                            <p class="text-sm text-gray-500 mt-1">Контейнер: {{ nginxStatus === 'running' ? '🟢' : '🔴' }} {{ nginxStatus }}</p>
                        </div>
                        <button
                            @click="fetchNginxStatus"
                            class="text-sm text-gray-600 hover:text-indigo-600"
                            :disabled="nginxLoading"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M3 12a9 9 0 1 1 9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M3 4v5h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button
                            @click="startNginx"
                            :disabled="nginxLoading"
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-green-50 text-green-700 font-medium hover:bg-green-100 transition disabled:opacity-50"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Start
                        </button>

                        <button
                            @click="stopNginx"
                            :disabled="nginxLoading"
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-700 font-medium hover:bg-red-100 transition disabled:opacity-50"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                            </svg>
                            Stop
                        </button>

                        <button
                            @click="restartNginx"
                            :disabled="nginxLoading"
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-indigo-50 text-indigo-700 font-medium hover:bg-indigo-100 transition disabled:opacity-50"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Restart
                        </button>

                        <button
                            @click="reloadNginx"
                            :disabled="nginxLoading"
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-50 text-blue-700 font-medium hover:bg-blue-100 transition disabled:opacity-50"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reload Config
                        </button>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Add host -->
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add host</h3>

                    <form class="space-y-4" @submit.prevent="createHost">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Domain</label>
                            <input
                                v-model="form.domain"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="app.local"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">
                                Port
                                <span class="text-xs text-gray-400">({{ availablePorts.length }} доступно)</span>
                            </label>
                            <select
                                v-model.number="form.port"
                                required
                                class="mt-1 block w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option :value="null" disabled>Оберіть порт</option>
                                <option
                                    v-for="port in availablePorts"
                                    :key="port"
                                    :value="port"
                                >
                                    {{ port }}
                                </option>
                            </select>
                            <p v-if="availablePorts.length === 0" class="mt-1 text-xs text-rose-500">
                                Всі порти зайняті (8081-8100)
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 text-white font-semibold py-2.5 hover:bg-indigo-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="creating || availablePorts.length === 0"
                        >
                            <span v-if="creating" class="h-4 w-4 border-2 border-white/50 border-t-white rounded-full animate-spin"></span>
                            <span v-if="availablePorts.length === 0">Немає вільних портів</span>
                            <span v-else>{{ creating ? 'Створення...' : 'Створити хост' }}</span>
                        </button>

                        <div v-if="errors" class="text-sm text-rose-500 space-y-1">
                            <div v-for="(messages, key) in errors" :key="key">
                                <p v-for="msg in messages" :key="msg">• {{ msg }}</p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Host list -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-center">
                        <h3 class="text-lg font-semibold text-gray-900">Existing hosts</h3>
                    </div>

                    <div v-if="loading" class="bg-white rounded-2xl p-8 text-center text-gray-500 border border-gray-100">
                        Loading hosts...
                    </div>

                    <div v-else-if="!hosts.length" class="bg-white rounded-2xl p-8 text-center border border-dashed border-gray-200 text-gray-500">
                        No hosts yet. Create your first vhost!
                    </div>

                    <div v-else class="grid md:grid-cols-2 gap-4">
                        <article
                            v-for="host in hosts"
                            :key="host.id"
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-4"
                        >
                            <div>
                                <p class="text-sm text-gray-500">Domain</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-xl font-semibold text-gray-900">{{ host.domain }}</p>
                                    <a
                                        :href="`http://0.0.0.0:${host.port}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 hover:underline transition"
                                        :title="`Відкрити http://0.0.0.0:${host.port}`"
                                    >
                                        відкрити
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-400">Port</p>
                                    <p class="text-lg font-medium text-indigo-600">{{ host.port }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                        :class="host.status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'"
                                    >
                                        {{ host.status === 'active' ? 'Active' : 'Disabled' }}
                                    </span>

                                    <button
                                        class="text-sm text-indigo-600 hover:text-indigo-500"
                                        @click="toggleHost(host)"
                                        :disabled="toggling === host.id"
                                    >
                                        {{ host.status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    class="text-sm text-rose-500 hover:text-rose-600"
                                    @click="deleteHost(host)"
                                    :disabled="deleting === host.id"
                                >
                                    Delete
                                </button>
                            </div>
                        </article>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
