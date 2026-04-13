<div>
    {{-- Progress indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach(['Business Info', 'Choose Plan', 'Your Account'] as $i => $label)
                @php $num = $i + 1; @endphp
                <div class="flex items-center {{ $i < 2 ? 'flex-1' : '' }}">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full text-sm font-medium
                                {{ $step > $num ? 'bg-primary-600 text-white' : ($step === $num ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-gray-100 text-gray-500') }}">
                        @if($step > $num)
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <span class="ml-2 text-sm font-medium {{ $step === $num ? 'text-primary-600' : 'text-gray-500' }} hidden sm:block">{{ $label }}</span>
                    @if($i < 2)
                        <div class="flex-1 h-px mx-4 {{ $step > $num ? 'bg-primary-600' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if($saveError)
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $saveError }}</div>
    @endif

    {{-- Step 1: Business Info --}}
    @if($step === 1)
        <h2 class="text-xl font-bold text-gray-900 mb-6">Tell us about your business</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Business Name *</label>
                <input wire:model="businessName" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="e.g. The Good Burger">
                @error('businessName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Business Email *</label>
                <input wire:model="businessEmail" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="info@mybusiness.com">
                @error('businessEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input wire:model="businessPhone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input wire:model="businessCity" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Currency</label>
                    <select wire:model="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="USD">USD — US Dollar</option>
                        <option value="EUR">EUR — Euro</option>
                        <option value="GBP">GBP — British Pound</option>
                        <option value="LKR">LKR — Sri Lankan Rupee</option>
                        <option value="INR">INR — Indian Rupee</option>
                        <option value="AUD">AUD — Australian Dollar</option>
                        <option value="CAD">CAD — Canadian Dollar</option>
                        <option value="SGD">SGD — Singapore Dollar</option>
                        <option value="AED">AED — UAE Dirham</option>
                        <option value="MYR">MYR — Malaysian Ringgit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Timezone</label>
                    <select wire:model="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">Eastern (US)</option>
                        <option value="America/Chicago">Central (US)</option>
                        <option value="America/Los_Angeles">Pacific (US)</option>
                        <option value="Europe/London">London</option>
                        <option value="Asia/Colombo">Colombo (LK)</option>
                        <option value="Asia/Kolkata">Mumbai (IN)</option>
                        <option value="Asia/Dubai">Dubai (AE)</option>
                        <option value="Asia/Singapore">Singapore</option>
                        <option value="Australia/Sydney">Sydney</option>
                    </select>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 2: Plan Selection --}}
    @if($step === 2)
        <h2 class="text-xl font-bold text-gray-900 mb-2">Choose your plan</h2>
        <p class="text-sm text-gray-500 mb-6">Start with the Free plan and upgrade anytime. All plans start with a 14-day trial.</p>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($plans as $plan)
                <div wire:click="$set('selectedPlanId', {{ $plan->id }})"
                     class="relative cursor-pointer rounded-xl border-2 p-5 transition-all
                            {{ $selectedPlanId === $plan->id ? 'border-primary-600 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                    @if($selectedPlanId === $plan->id)
                        <div class="absolute top-3 right-3">
                            <div class="h-5 w-5 rounded-full bg-primary-600 flex items-center justify-center">
                                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                        </div>
                    @endif
                    <h3 class="font-semibold text-gray-900">{{ $plan->name }}</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $plan->formattedPrice() }}
                        @if($plan->price_monthly > 0)
                            <span class="text-sm font-normal text-gray-500">/month</span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-500 mt-2">{{ $plan->description }}</p>
                    <ul class="mt-3 space-y-1 text-xs text-gray-600">
                        <li>✓ Up to {{ number_format($plan->max_orders_per_month) }} orders/month</li>
                        <li>✓ Up to {{ $plan->max_staff }} staff users</li>
                        <li>✓ Up to {{ $plan->max_menu_items }} menu items</li>
                        @if($plan->hasFeature('hr_module')) <li>✓ HR & Attendance module</li> @endif
                        @if($plan->hasFeature('reports_export')) <li>✓ Reports export (PDF/CSV)</li> @endif
                        @if($plan->hasFeature('sms_notifications')) <li>✓ SMS notifications</li> @endif
                    </ul>
                </div>
            @endforeach
        </div>
        @error('selectedPlanId') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    @endif

    {{-- Step 3: Admin Account --}}
    @if($step === 3)
        <h2 class="text-xl font-bold text-gray-900 mb-6">Create your admin account</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                <input wire:model="adminName" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('adminName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Login Email *</label>
                <input wire:model="adminEmail" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('adminEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password *</label>
                <input wire:model="adminPassword" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('adminPassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                <input wire:model="adminPasswordConfirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
            </div>
            <div class="flex items-start gap-3">
                <input wire:model="agreeTerms" type="checkbox" id="agreeTerms" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600">
                <label for="agreeTerms" class="text-sm text-gray-600">
                    I agree to the <a href="#" class="text-primary-600 hover:underline">Terms of Service</a> and <a href="#" class="text-primary-600 hover:underline">Privacy Policy</a>
                </label>
            </div>
            @error('agreeTerms') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endif

    {{-- Navigation buttons --}}
    <div class="mt-8 flex items-center justify-between">
        @if($step > 1)
            <button type="button" wire:click="prevStep" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                ← Back
            </button>
        @else
            <div></div>
        @endif

        @if($step < $totalSteps)
            <button type="button" wire:click="nextStep"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-md text-sm font-medium hover:bg-primary-700 disabled:opacity-50"
                    wire:loading.attr="disabled">
                Continue →
            </button>
        @else
            <button type="button" wire:click="register"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-md text-sm font-medium hover:bg-primary-700 disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Create Account</span>
                <span wire:loading>Creating...</span>
            </button>
        @endif
    </div>

    <p class="mt-6 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500">Sign in</a>
    </p>
</div>
