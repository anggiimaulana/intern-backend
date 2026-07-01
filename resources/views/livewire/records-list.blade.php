<div x-data="recordsApp()" x-init="init()" class="flex flex-col gap-4 h-[calc(100dvh-14rem)]">
    <!-- Performance Monitoring Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 text-white p-4 rounded-xl shadow-lg">
        <div class="flex items-center space-x-2">
            <div class="p-2 bg-indigo-600/30 rounded-lg"><svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/></svg></div>
            <div>
                <p class="text-xs text-indigo-200 uppercase tracking-wider">Total Records</p>
                <p class="text-lg font-bold font-mono" x-text="formatNumber(displayTotal)"></p>
                <p class="text-xs text-indigo-300" x-show="filteredTotal !== null" x-text="'of ' + formatNumber(total) + ' total'"></p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <div class="p-2 bg-green-600/30 rounded-lg"><svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            <div>
                <p class="text-xs text-green-200 uppercase tracking-wider">Load Speed</p>
                <p class="text-lg font-bold font-mono text-green-400" x-text="loadSpeedMs + ' ms'"></p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <div class="p-2 bg-blue-600/30 rounded-lg"><svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
            <div>
                <p class="text-xs text-blue-200 uppercase tracking-wider">Index</p>
                <p class="text-sm font-bold text-blue-300" x-text="querySpeed"></p>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col gap-3">
            <div class="flex flex-wrap gap-3 items-end">
                <!-- Sort -->
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Sort</label>
                    <select x-model="sort" @change="resetAndLoad()" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="desc">⬇ Terbaru (ID Desc)</option>
                        <option value="asc">⬆ Terlama (ID Asc)</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="min-w-[120px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Per Page</label>
                    <select x-model="perPage" @change="onPerPageChange()" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="500">500</option>
                        <option value="1000">1.000</option>
                        <option value="custom">Custom</option>
                    </select>
                    <div x-show="perPage === 'custom'" class="mt-1">
                        <input type="number" x-model="customPerPage" @input.debounce="resetAndLoad()" placeholder="Custom amount" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                </div>

                <!-- ID From -->
                <div class="min-w-[120px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID From</label>
                    <input type="number" x-model="idFrom" placeholder="Min ID" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- ID To -->
                <div class="min-w-[120px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID To</label>
                    <input type="number" x-model="idTo" placeholder="Max ID" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Search -->
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search</label>
                    <input type="text" x-model="search" placeholder="Search name, email, status..." class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button @click="applyFilters()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Apply</button>
                    <button @click="resetFilters()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">Reset</button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500" x-text="'Showing ' + records.length + ' of ' + formatNumber(displayTotal) + ' records'"></p>
                <button @click="openAddModal()" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Record
                </button>
            </div>
        </div>
    </div>

    <!-- Loading indicator for initial/page loads -->
    <div x-show="loading" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden p-4">
        <div class="space-y-3">
            <template x-for="i in 5" :key="i">
                <div class="flex gap-4 py-2 border-b border-gray-100 dark:border-gray-700">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-12"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-20"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-32"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-48"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-24"></div>
                </div>
            </template>
        </div>
    </div>

    <!-- Data Table (visible when not loading) -->
    <div x-show="!loading" x-ref="tableContainer" class="flex-1 overflow-y-auto bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">No.</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(row, index) in records" :key="row.id">
                        <tr style="content-visibility: auto" class="hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 transition">
                            <td class="px-4 py-2.5 text-sm font-bold text-gray-900 dark:text-gray-100 font-mono" x-text="index + 1"></td>
                            <td class="px-4 py-2.5 text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="'#' + row.id"></td>
                            <td class="px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="row.field_1"></td>
                            <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300 font-mono" x-text="row.field_2"></td>
                            <td class="px-4 py-2.5 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': row.field_3 === 'Active' || row.field_3 === 'Verified',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': row.field_3 === 'Pending',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200': !['Active', 'Verified', 'Pending'].includes(row.field_3)
                                    }"
                                    x-text="row.field_3">
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right text-sm space-x-2">
                                <button @click="openEditModal(row)" class="text-indigo-600 hover:text-indigo-900 font-semibold transition">Edit</button>
                                <button @click="deleteRecord(row.id)" class="text-red-600 hover:text-red-900 font-semibold transition">Delete</button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="records.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Infinite scroll sentinel -->
        <div x-ref="sentinel" class="h-4"></div>

        <!-- Loading more indicator -->
        <div x-show="loadingMore" class="py-4 text-center">
            <svg class="inline animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm text-gray-500 ml-2">Loading more...</span>
        </div>

        <!-- End of list indicator -->
        <div x-show="!hasMore && records.length > 0" class="py-4 text-center text-sm text-gray-400">
            — All records loaded —
        </div>
    </div>

    <!-- Add Modal -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeModals()">
        <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Add Record</h3>
            <form @submit.prevent="createRecord()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input type="text" x-model="formName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" x-model="formEmail" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select x-model="formStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="Active">Active</option>
                        <option value="Pending">Pending</option>
                        <option value="Verified">Verified</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="closeModals()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeModals()">
        <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4" x-text="'Edit Record #' + editingId"></h3>
            <form @submit.prevent="updateRecord()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input type="text" x-model="formName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" x-model="formEmail" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select x-model="formStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="Active">Active</option>
                        <option value="Pending">Pending</option>
                        <option value="Verified">Verified</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="closeModals()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function recordsApp() {
            return {
                records: [],
                nextCursor: null,
                total: 0,
                filteredTotal: null,
                loadSpeedMs: 0,
                querySpeed: '',
                loading: true,
                loadingMore: false,
                hasMore: true,

                sort: 'desc',
                perPage: '500',
                customPerPage: '',
                idFrom: '',
                idTo: '',
                search: '',

                showAddModal: false,
                showEditModal: false,
                editingId: null,
                formName: '',
                formEmail: '',
                formStatus: 'Active',

                observer: null,

                get displayTotal() {
                    return this.filteredTotal ?? this.total;
                },

                get actualLimit() {
                    if (this.perPage === 'custom') return parseInt(this.customPerPage) || 500;
                    if (this.perPage === 'all') return 9999999;
                    return parseInt(this.perPage) || 500;
                },

                formatNumber(n) {
                    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
                    if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
                    return n.toString();
                },

                get csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                },

                buildUrl(cursor) {
                    const params = new URLSearchParams();
                    params.set('limit', this.actualLimit);
                    params.set('sort', this.sort);
                    if (cursor) params.set('cursor', cursor);
                    if (this.idFrom) params.set('id_from', this.idFrom);
                    if (this.idTo) params.set('id_to', this.idTo);
                    if (this.search && this.search.trim().length >= 2) params.set('search', this.search.trim());
                    return '/records/data?' + params.toString();
                },

                async loadFirstPage() {
                    this.loading = true;
                    this.records = [];
                    this.nextCursor = null;
                    this.hasMore = true;

                    const start = performance.now();
                    try {
                        const res = await fetch(this.buildUrl(null));
                        const data = await res.json();
                        this.records = data.data || [];
                        this.nextCursor = data.next_cursor;
                        this.total = data.total;
                        this.filteredTotal = data.filtered_total;
                        this.querySpeed = data.query_execution_speed || '';
                        this.loadSpeedMs = ((performance.now() - start) / 1000).toFixed(2);

                        if (!this.nextCursor) this.hasMore = false;

                        this.$nextTick(() => this.observeSentinel());
                    } catch (e) {
                        console.error('Failed to load records', e);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadMore() {
                    if (this.loadingMore || !this.hasMore || !this.nextCursor) return;
                    this.loadingMore = true;

                    try {
                        const res = await fetch(this.buildUrl(this.nextCursor));
                        const data = await res.json();
                        const newRecords = data.data || [];
                        this.records = this.records.concat(newRecords);
                        this.nextCursor = data.next_cursor;
                        this.filteredTotal = data.filtered_total;
                        this.querySpeed = data.query_execution_speed || '';

                        if (!this.nextCursor || newRecords.length === 0) this.hasMore = false;
                    } catch (e) {
                        console.error('Failed to load more', e);
                    } finally {
                        this.loadingMore = false;
                    }
                },

                observeSentinel() {
                    if (this.observer) this.observer.disconnect();

                    const sentinel = this.$refs.sentinel;
                    const container = this.$refs.tableContainer;
                    if (!sentinel) return;

                    this.observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) this.loadMore();
                    }, { root: container, rootMargin: '200px' });

                    this.observer.observe(sentinel);
                },

                resetAndLoad() {
                    if (this.observer) this.observer.disconnect();
                    this.loadFirstPage();
                },

                onPerPageChange() {
                    if (this.perPage !== 'custom') this.resetAndLoad();
                },

                applyFilters() {
                    this.resetAndLoad();
                },

                resetFilters() {
                    this.sort = 'desc';
                    this.perPage = '500';
                    this.customPerPage = '';
                    this.idFrom = '';
                    this.idTo = '';
                    this.search = '';
                    this.resetAndLoad();
                },

                openAddModal() {
                    this.formName = '';
                    this.formEmail = '';
                    this.formStatus = 'Active';
                    this.showAddModal = true;
                },

                closeModals() {
                    this.showAddModal = false;
                    this.showEditModal = false;
                    this.editingId = null;
                },

                async createRecord() {
                    try {
                        const res = await fetch('/records/data', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify({ field_1: this.formName, field_2: this.formEmail, field_3: this.formStatus }),
                        });
                        if (!res.ok) { const err = await res.json(); alert(err.message || 'Failed to create'); return; }
                        this.closeModals();
                        this.resetAndLoad();
                    } catch (e) {
                        alert('Network error');
                    }
                },

                openEditModal(row) {
                    this.editingId = row.id;
                    this.formName = row.field_1;
                    this.formEmail = row.field_2;
                    this.formStatus = row.field_3;
                    this.showEditModal = true;
                },

                async updateRecord() {
                    try {
                        const res = await fetch('/records/data/' + this.editingId, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify({ field_1: this.formName, field_2: this.formEmail, field_3: this.formStatus }),
                        });
                        if (!res.ok) { const err = await res.json(); alert(err.message || 'Failed to update'); return; }
                        this.closeModals();
                        this.resetAndLoad();
                    } catch (e) {
                        alert('Network error');
                    }
                },

                async deleteRecord(id) {
                    if (!confirm('Delete this record?')) return;
                    try {
                        const res = await fetch('/records/data/' + id, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                        });
                        if (!res.ok) { alert('Failed to delete'); return; }
                        this.resetAndLoad();
                    } catch (e) {
                        alert('Network error');
                    }
                },

                init() {
                    this.loadFirstPage();
                }
            };
        }
    </script>
</div>
