// Project Edit Alpine.js Components
document.addEventListener('alpine:init', () => {
    // Project Edit Tabs Component
    Alpine.data('projectEditTabs', () => ({
        activeTab: localStorage.getItem('projectEditTab') || 'general',
        hasErrors: {
            general: false,
            billing: false,
            team: false,
            ai: false,
            structure: false,
            media: false
        },
        isDirty: false,
        isSubmitting: false,
        isReloading: false,
        showUnsavedModal: false,
        pendingNavigation: null,
        billingFrequency: 'monthly',
        
        // Month filtering state
        currentMonth: new Date().toISOString().slice(0, 7), // YYYY-MM format
        currentMonthDisplay: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long' }),
        monthData: null,
        monthLoading: false,
        monthStats: {
            total_milestones: 0,
            completed_milestones: 0,
            total_tasks: 0,
            completed_tasks: 0,
            total_hours: 0,
            total_costs: 0
        },

        // Modal state and functions
        showMilestoneModal: false,
        showTaskModal: false,
        currentMilestoneId: null,
        editingMilestoneId: null,
        editingTaskId: null,
        
        milestoneForm: {
            name: '',
            description: '',
            start_date: '',
            end_date: '',
            status: 'pending',
            fee_type: 'in_fee',
            pricing_type: 'hourly_rate',
            fixed_price: null,
            hourly_rate_override: null,
            estimated_hours: null,
            invoicing_trigger: 'completion',
            deliverables: '',
            saving: false
        },
        
        taskForm: {
            name: '',
            description: '',
            fee_type: 'in_fee',
            pricing_type: 'hourly_rate',
            fixed_price: null,
            hourly_rate_override: null,
            estimated_hours: null,
            status: 'pending',
            priority: 'medium',
            start_date: null,
            end_date: null,
            completion_percentage: 0,
            actual_hours: 0,
            notes: '',
            saving: false
        },

        // Initialization
        init() {
            // Initialize billing frequency from PHP if available
            const billingSelect = document.querySelector('select[name="billing_frequency"]');
            if (billingSelect) {
                this.billingFrequency = billingSelect.value || 'monthly';
            }

            // Initialize current month from URL params
            const urlParams = new URLSearchParams(window.location.search);
            const monthParam = urlParams.get('month');
            if (monthParam) {
                this.currentMonth = monthParam;
                try {
                    const date = new Date(monthParam + '-01');
                    this.currentMonthDisplay = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
                } catch (e) {
                    console.warn('Invalid month parameter:', monthParam);
                }
            }

            // Listen for form changes to mark as dirty
            this.$nextTick(() => {
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('input', () => {
                        this.isDirty = true;
                    });
                    form.addEventListener('change', () => {
                        this.isDirty = true;
                    });
                });
            });

            // Don't auto-load month data on initialization to avoid conflicts
            // this.loadMonthData();
        },

        // Tab switching
        switchTab(tab) {
            if (this.isDirty && this.activeTab !== tab) {
                this.pendingNavigation = tab;
                this.showUnsavedModal = true;
                return;
            }
            
            this.activeTab = tab;
            localStorage.setItem('projectEditTab', tab);
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },

        // Month navigation
        navigateMonth(direction) {
            if (this.monthLoading) return;
            
            this.monthLoading = true;
            const currentDate = new Date(this.currentMonth + '-01');
            
            if (direction === 'prev') {
                currentDate.setMonth(currentDate.getMonth() - 1);
            } else if (direction === 'next') {
                currentDate.setMonth(currentDate.getMonth() + 1);
            } else {
                // Today
                const today = new Date();
                currentDate.setFullYear(today.getFullYear());
                currentDate.setMonth(today.getMonth());
            }
            
            this.currentMonth = currentDate.toISOString().slice(0, 7);
            this.currentMonthDisplay = currentDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('month', this.currentMonth);
            window.history.replaceState({}, '', url);
            
            this.loadMonthData();
        },

        // Load month data
        async loadMonthData() {
            if (this.monthLoading) return;
            
            this.monthLoading = true;
            
            try {
                const projectId = window.location.pathname.split('/')[2];
                const url = `/projects/${projectId}/month-data?month=${this.currentMonth}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const data = await response.json();
                        if (data.success) {
                            this.monthData = data.monthData;
                            this.monthStats = data.stats || this.monthStats;
                            
                            // Update the milestone/task display
                            this.$nextTick(() => {
                                this.reinitializeDragDrop();
                            });
                        }
                    } else {
                        console.error('Response is not JSON, got:', contentType);
                        // Fallback: reload the page to get fresh data
                        window.location.href = `/projects/${projectId}/edit?month=${this.currentMonth}`;
                    }
                } else {
                    console.error('Failed to load month data:', response.status, response.statusText);
                }
            } catch (error) {
                console.error('Error loading month data:', error);
                // Don't reload on initialization, only on navigation
                if (this.currentMonth !== new Date().toISOString().slice(0, 7)) {
                    const projectId = window.location.pathname.split('/')[2];
                    window.location.href = `/projects/${projectId}/edit?month=${this.currentMonth}`;
                }
            } finally {
                this.monthLoading = false;
            }
        },

        // Modal functions
        openMilestoneModal() {
            this.showMilestoneModal = true;
            this.editingMilestoneId = null;
            this.resetMilestoneForm();
        },

        closeMilestoneModal() {
            this.showMilestoneModal = false;
            this.editingMilestoneId = null;
        },

        openTaskModal(milestoneId) {
            this.showTaskModal = true;
            this.currentMilestoneId = milestoneId;
            this.editingTaskId = null;
            this.resetTaskForm();
        },

        closeTaskModal() {
            this.showTaskModal = false;
            this.currentMilestoneId = null;
            this.editingTaskId = null;
            this.resetTaskForm();
        },

        resetMilestoneForm() {
            this.milestoneForm = {
                name: '',
                description: '',
                start_date: '',
                end_date: '',
                status: 'pending',
                fee_type: 'in_fee',
                pricing_type: 'hourly_rate',
                fixed_price: null,
                hourly_rate_override: null,
                estimated_hours: null,
                invoicing_trigger: 'completion',
                deliverables: '',
                saving: false
            };
        },

        resetTaskForm() {
            this.taskForm = {
                name: '',
                description: '',
                fee_type: 'in_fee',
                pricing_type: 'hourly_rate',
                fixed_price: null,
                hourly_rate_override: null,
                estimated_hours: null,
                status: 'pending',
                priority: 'medium',
                start_date: null,
                end_date: null,
                completion_percentage: 0,
                actual_hours: 0,
                notes: '',
                saving: false
            };
        },

        // Edit milestone function
        editMilestone(id, name, description, start_date, end_date, status, fee_type, pricing_type, fixed_price, hourly_rate_override, estimated_hours, invoicing_trigger, deliverables) {
            this.editingMilestoneId = id;
            this.milestoneForm = {
                name: name || '',
                description: description || '',
                start_date: start_date || '',
                end_date: end_date || '',
                status: status || 'pending',
                fee_type: fee_type || 'in_fee',
                pricing_type: pricing_type || 'hourly_rate',
                fixed_price: fixed_price || null,
                hourly_rate_override: hourly_rate_override || null,
                estimated_hours: estimated_hours || null,
                invoicing_trigger: invoicing_trigger || 'completion',
                deliverables: deliverables || '',
                saving: false
            };
            this.showMilestoneModal = true;
        },

        // Save functions
        async saveMilestone() {
            if (this.milestoneForm.saving) return;
            
            this.milestoneForm.saving = true;
            
            try {
                const projectId = window.location.pathname.split('/')[2];
                const url = this.editingMilestoneId 
                    ? `/projects/${projectId}/milestones/${this.editingMilestoneId}`
                    : `/projects/${projectId}/milestones`;
                const method = this.editingMilestoneId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.milestoneForm)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.closeMilestoneModal();
                    this.loadMonthData(); // Refresh data
                    this.showSuccessMessage(this.editingMilestoneId ? 'Milestone updated successfully' : 'Milestone created successfully');
                } else {
                    throw new Error(result.message || 'Failed to save milestone');
                }
            } catch (error) {
                console.error('Error saving milestone:', error);
                this.showErrorMessage('Error saving milestone: ' + error.message);
            } finally {
                this.milestoneForm.saving = false;
            }
        },

        async saveTask() {
            if (this.taskForm.saving || !this.currentMilestoneId) return;
            
            this.taskForm.saving = true;
            
            try {
                const response = await fetch(`/project-milestones/${this.currentMilestoneId}/tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.taskForm)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.closeTaskModal();
                    this.loadMonthData(); // Refresh data
                    this.showSuccessMessage('Task created successfully');
                } else {
                    throw new Error(result.message || 'Failed to save task');
                }
            } catch (error) {
                console.error('Error saving task:', error);
                this.showErrorMessage('Error saving task: ' + error.message);
            } finally {
                this.taskForm.saving = false;
            }
        },

        async updateTask() {
            if (this.taskForm.saving || !this.editingTaskId) return;
            
            this.taskForm.saving = true;
            
            try {
                const response = await fetch(`/project-milestones/${this.currentMilestoneId}/tasks/${this.editingTaskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.taskForm)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.closeTaskModal();
                    this.loadMonthData(); // Refresh data
                    this.showSuccessMessage('Task updated successfully');
                } else {
                    throw new Error(result.message || 'Failed to update task');
                }
            } catch (error) {
                console.error('Error updating task:', error);
                this.showErrorMessage('Error updating task: ' + error.message);
            } finally {
                this.taskForm.saving = false;
            }
        },

        // Edit task function
        editTask(id, milestoneId, name, description, start_date, end_date, status, fee_type, pricing_type, fixed_price, hourly_rate_override, estimated_hours, is_service_item, service_name) {
            this.editingTaskId = id;
            this.currentMilestoneId = milestoneId;
            this.taskForm = {
                name: name || '',
                description: description || '',
                start_date: start_date || null,
                end_date: end_date || null,
                status: status || 'pending',
                fee_type: fee_type || 'in_fee',
                pricing_type: pricing_type || 'hourly_rate',
                fixed_price: fixed_price || null,
                hourly_rate_override: hourly_rate_override || null,
                estimated_hours: estimated_hours || null,
                priority: 'medium',
                completion_percentage: 0,
                actual_hours: 0,
                notes: '',
                saving: false
            };
            this.showTaskModal = true;
        },

        // Utility functions
        showSuccessMessage(message) {
            // Create temporary success notification
            const div = document.createElement('div');
            div.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            div.textContent = message;
            document.body.appendChild(div);
            
            setTimeout(() => {
                div.remove();
            }, 3000);
        },

        showErrorMessage(message) {
            // Create temporary error notification
            const div = document.createElement('div');
            div.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            div.textContent = message;
            document.body.appendChild(div);
            
            setTimeout(() => {
                div.remove();
            }, 5000);
        },

        // Reinitialize drag & drop after content update
        reinitializeDragDrop() {
            // Wait a bit for DOM to settle, then reinitialize drag & drop
            setTimeout(() => {
                if (window.initializeDragDrop) {
                    console.log('Reinitializing drag & drop after data update');
                    window.initializeDragDrop();
                }
            }, 100);
        }
    }));

    // Team Member Selector Component
    Alpine.data('teamMemberSelector', () => ({
        search: '',
        showDropdown: false,
        allUsers: [],
        filteredUsers: [],
        selectedUsers: [], // Track selected team members

        init() {
            // Initialize users from PHP data if available
            const usersScript = document.querySelector('#team-users-data');
            if (usersScript) {
                try {
                    this.allUsers = JSON.parse(usersScript.textContent);
                    this.filteredUsers = this.allUsers;
                } catch (e) {
                    console.warn('Could not parse users data:', e);
                }
            }

            // Initialize selected users from existing team members
            const existingMembers = document.querySelectorAll('[data-user-id]');
            this.selectedUsers = Array.from(existingMembers).map(el => 
                parseInt(el.dataset.userId)
            );
        },

        searchUsers() {
            if (!this.search.trim()) {
                this.filteredUsers = this.allUsers;
                return;
            }
            
            const searchTerm = this.search.toLowerCase();
            this.filteredUsers = this.allUsers.filter(user => 
                user.name.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm)
            );
        },

        selectUser(user) {
            // Add user to selected list if not already selected
            if (!this.isSelected(user.id)) {
                this.selectedUsers.push(user.id);
            }
            
            console.log('Selected user:', user);
            this.search = '';
            this.showDropdown = false;
            this.filteredUsers = this.allUsers;
        },

        removeUser(userId) {
            this.selectedUsers = this.selectedUsers.filter(id => id !== userId);
        },

        isSelected(userId) {
            return this.selectedUsers.includes(userId);
        },

        getSelectedUser(userId) {
            return this.allUsers.find(user => user.id === userId);
        }
    }));
});