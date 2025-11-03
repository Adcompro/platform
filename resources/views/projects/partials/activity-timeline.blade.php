{{-- Project Activity Timeline --}}
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Project Activity</h2>
        <p class="text-sm text-gray-600 mt-1">Track all changes and time entries for this project</p>
    </div>

    <div class="p-6">
        @if($activities->count() > 0)
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($activities as $index => $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif

                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $activity->activity_badge_class }}">
                                            <i class="{{ $activity->activity_icon }} text-xs"></i>
                                        </span>
                                    </div>

                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $activity->user->name }}</span>
                                                <span class="text-gray-600">{{ $activity->description }}</span>
                                            </p>

                                            {{-- Show changed fields if available --}}
                                            @if($activity->old_values || $activity->new_values)
                                                <div class="mt-2 text-xs bg-gray-50 rounded-md p-2 border border-gray-200">
                                                    <div class="font-semibold text-gray-700 mb-1">Changed Fields:</div>
                                                    @if($activity->old_values)
                                                        @foreach($activity->old_values as $field => $oldValue)
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                                @if($oldValue)
                                                                    <span class="text-red-600 line-through">{{ $oldValue }}</span>
                                                                @endif
                                                                @if(isset($activity->new_values[$field]))
                                                                    <span class="text-gray-400">→</span>
                                                                    <span class="text-green-600 font-medium">{{ $activity->new_values[$field] }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Entity type badge --}}
                                            @if($activity->entity_type)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-2">
                                                    {{ ucfirst($activity->entity_type) }}
                                                    @if($activity->entity_id)
                                                        #{{ $activity->entity_id }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                            <div>{{ $activity->created_at->format('d-m-Y H:i:s') }}</div>
                                            <div class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</div>
                                            @if($activity->ip_address)
                                                <div class="text-xs text-gray-400 mt-1">IP: {{ $activity->ip_address }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $activities->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-history text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No activity yet</h3>
                <p class="text-sm text-gray-500">Activity will appear here as changes are made to the project</p>
            </div>
        @endif
    </div>
</div>
