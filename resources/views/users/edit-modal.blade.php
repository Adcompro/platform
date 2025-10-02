{{-- Modal Edit Form for User --}}
<div>
    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Basic User Information --}}
        <div class="space-y-4">
            {{-- Display Name --}}
            <div>
                <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Display Name <span style="color: var(--theme-danger);">*</span>
                </label>
                <input type="text" name="name" id="name" required
                       value="{{ old('name', $user->name) }}"
                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                       placeholder="John Doe">
            </div>

            {{-- Email --}}
            <div>
                <label for="email" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Email Address <span style="color: var(--theme-danger);">*</span>
                </label>
                <input type="email" name="email" id="email" required
                       value="{{ old('email', $user->email) }}"
                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                       placeholder="john.doe@company.com">
                @if(!$user->hasVerifiedEmail())
                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: #f59e0b;">
                        Email is not verified
                    </p>
                @endif
            </div>

            {{-- Role --}}
            <div>
                <label for="role" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Role <span style="color: var(--theme-danger);">*</span>
                </label>
                <select name="role" id="role" required
                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                    <option value="project_manager" {{ old('role', $user->role) === 'project_manager' ? 'selected' : '' }}>Project Manager</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="reader" {{ old('role', $user->role) === 'reader' ? 'selected' : '' }}>Reader</option>
                    @if(Auth::user()->role === 'super_admin')
                        <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    @endif
                </select>
                <p style="margin-top: 0.5rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                    Select the user's role and permissions level
                </p>
            </div>

            {{-- Company (Super Admin only) --}}
            @if(Auth::user()->role === 'super_admin' && isset($companies))
            <div>
                <label for="company_id" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Company
                </label>
                <select name="company_id" id="company_id"
                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                    <option value="">No Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Status --}}
            <div>
                <label for="is_active" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Status
                </label>
                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="radio" name="is_active" value="1"
                               {{ old('is_active', $user->is_active) == '1' ? 'checked' : '' }}
                               class="h-4 w-4 border-gray-300 rounded"
                               style="color: var(--theme-primary);">
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Active</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="is_active" value="0"
                               {{ old('is_active', $user->is_active) == '0' ? 'checked' : '' }}
                               class="h-4 w-4 border-gray-300 rounded"
                               style="color: var(--theme-primary);">
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Inactive</span>
                    </label>
                </div>
            </div>

            {{-- Auto-approve Time Entries --}}
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="auto_approve_time_entries" value="1"
                           {{ old('auto_approve_time_entries', $user->auto_approve_time_entries) ? 'checked' : '' }}
                           class="h-4 w-4 border-gray-300 rounded"
                           style="color: var(--theme-primary);">
                    <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Auto-approve time entries</span>
                </label>
                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                    Automatically approve time entries submitted by this user
                </p>
            </div>

            {{-- Optional Contact Information --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Phone --}}
                <div>
                    <label for="phone" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Phone
                    </label>
                    <input type="text" name="phone" id="phone"
                           value="{{ old('phone', $user->phone) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="+1234567890">
                </div>

                {{-- Department --}}
                <div>
                    <label for="department" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Department
                    </label>
                    <input type="text" name="department" id="department"
                           value="{{ old('department', $user->department) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="Engineering">
                </div>
            </div>

            {{-- Password Reset Option --}}
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="send_password_reset" value="1"
                           class="h-4 w-4 border-gray-300 rounded"
                           style="color: var(--theme-primary);">
                    <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Send password reset email</span>
                </label>
                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                    Send a password reset email to allow the user to set a new password
                </p>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                <span style="color: var(--theme-danger);">*</span> Required fields
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="closeEditUserModal()"
                        style="padding: 0.5rem 1rem; background-color: #6b7280; color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                    Cancel
                </button>
                <button type="submit"
                        style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                    <i class="fas fa-save mr-1.5"></i>
                    Update User
                </button>
            </div>
        </div>
    </form>
</div>