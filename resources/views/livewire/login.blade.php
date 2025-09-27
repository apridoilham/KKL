@section('title', $data['title'] ?? 'Login Page')
{{-- Kita gunakan class dari Tailwind CSS untuk membuat layout --}}
<div class="bg-slate-100 flex items-center justify-center min-h-screen font-sans">

    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-lg">
        
        <div class="flex flex-col items-center space-y-2">
            <svg class="w-16 h-16 text-slate-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22 8.5h-3.5v-3a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v3H1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h21a1 1 0 0 0 1-1v-10a1 1 0 0 0-1-1Zm-16.5-2h10v2h-10Zm15.5 12H2v-8h20Z"/>
                <path d="M10.5 14.5h-4a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5Zm-3.5-1h3v-1h-3Z"/>
            </svg>
            <h1 class="text-2xl font-bold text-slate-800 text-center">
                {{ $checkData > 0 ? 'Welcome Back!' : 'Create Admin Account' }}
            </h1>
            <p class="text-sm text-slate-500">Inventory Management System</p>
        </div>

        <form class="space-y-4" wire:submit.prevent='submit'>
            
            <div>
                <label for="username" class="block text-sm font-medium text-slate-600">Username</label>
                <input wire:model='username' id="username" type="text" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                @error('username') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-slate-600">Password</label>
                <input wire:model='password' id="password" type="password" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            @if ($checkData == 0)
                <hr class="border-slate-200">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-600">Full Name</label>
                    <input wire:model='name' id="name" type="text" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm" required>
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="securityQuestion" class="block text-sm font-medium text-slate-600">Security Question</label>
                    <input wire:model='securityQuestion' id="securityQuestion" type="text" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm" placeholder="e.g., Your pet's name?" required>
                    @error('securityQuestion') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="securityAnswer" class="block text-sm font-medium text-slate-600">Security Answer</label>
                    <input wire:model='securityAnswer' id="securityAnswer" type="text" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm" required>
                    @error('securityAnswer') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            @endif
            
            @if (session()->has('dataSession'))
                <div class="text-center text-sm {{ session('dataSession')->status == 'success' ? 'text-green-600' : 'text-red-600' }}">
                    {{ session('dataSession')->message }}
                </div>
            @endif

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    {{ $checkData > 0 ? 'Sign In' : 'Create Admin & Sign In' }}
                </button>
            </div>
        </form>

        <div class="text-center">
            <a href="#" wire:click.prevent="$set('isModalOpen', true)" class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline">
                Forgot Password?
            </a>
        </div>
    </div>

    {{-- Modal Lupa Password (tidak diubah, masih fungsional) --}}
    @if ($isModalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <div class="modal-content">
                    <div class="modal-header px-6 py-4 border-b">
                        <h5 class="modal-title text-lg font-medium">Reset Password</h5>
                        <button type="button" class="text-slate-400 hover:text-slate-600" wire:click="$set('isModalOpen', false)">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-6 space-y-4">
                        @if (session()->has('dataSession2'))
                            <div class="alert alert-{{ session('dataSession2')->status == 'success' ? 'info' : 'danger' }} small py-2">
                                {{ session('dataSession2')->message }}
                            </div>
                        @endif

                        @if(!$isVerified)
                            <div class="form-group">
                                <label>Enter your username</label>
                                <input wire:model.lazy='username' type="text" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm" required>
                                @error('username') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        
                        @if($isUserFound && !$isVerified)
                            <div class="form-group">
                                <label class="font-medium">Security Question:</label>
                                <p class="mt-1 p-2 bg-slate-100 rounded-md">{{ $securityQuestion }}</p>
                            </div>
                            <div class="form-group">
                                <label for="securityAnswerModal">Security Answer:</label>
                                <input wire:model.lazy='securityAnswer' id="securityAnswerModal" type="text" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm" required>
                                @error('securityAnswer') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        
                        @if ($isVerified)
                            <div class="form-group">
                                 <label for="newPassword">New Password</label>
                                <input wire:model='newPassword' id="newPassword" type="password" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm">
                                @error('newPassword') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="confPass">Confirm Password</label>
                                <input wire:model='confPass' id="confPass" type="password" class="mt-1 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm">
                                @error('confPass') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer px-6 py-3 bg-slate-50 text-right">
                        @if(!$isVerified)
                            <button type="button" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700" wire:click='verifyData'>Verify</button>
                        @else
                            <button type="button" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700" wire:click='changePassword'>Save New Password</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>