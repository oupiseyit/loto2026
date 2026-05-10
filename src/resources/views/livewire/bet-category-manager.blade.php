<div>
    {{-- Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge badge-pill mr-1"
                  style="background-color:#DC143C;color:#fff;font-size:.8rem;">
                {{ $categories->where('type', 1)->count() }} P
            </span>
            <span class="badge badge-pill"
                  style="background-color:#D4A017;color:#fff;font-size:.8rem;">
                {{ $categories->where('type', 2)->count() }} LO
            </span>
        </div>
        <button wire:click="openCreate" type="button"
                class="btn btn-sm font-weight-bold"
                style="background-color:#DC143C;color:#fff;">
            <i class="fas fa-plus mr-1"></i> Add Category
        </button>
    </div>

    {{-- Table --}}
    @if ($categories->isEmpty())
        <p class="text-center text-muted py-4">No categories yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead>
                    <tr style="background-color:#DC143C;color:#fff;">
                        <th style="width:50px;">#</th>
                        <th>Name</th>
                        <th style="width:90px;" class="text-center">Type</th>
                        <th style="width:100px;" class="text-center">Status</th>
                        <th style="width:110px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $c)
                        <tr>
                            <td class="text-muted text-center align-middle">{{ $c->sort_order }}</td>
                            <td class="font-weight-bold align-middle">{{ $c->name }}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill"
                                      style="{{ $c->type === 1 ? 'background-color:#DC143C;color:#fff;' : 'background-color:#D4A017;color:#fff;' }}">
                                    {{ $c->type_label }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <button wire:click="toggleStatus({{ $c->id }})" type="button"
                                        class="btn btn-xs rounded-pill"
                                        style="{{ $c->status ? 'background-color:#d1fae5;color:#065f46;' : 'background-color:#fee2e2;color:#991b1b;' }}">
                                    {{ $c->status ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="text-center align-middle">
                                <button wire:click="openEdit({{ $c->id }})" type="button"
                                        class="btn btn-xs btn-outline-warning mr-1"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $c->id }})"
                                        wire:confirm="Delete category '{{ $c->name }}'?"
                                        type="button"
                                        class="btn btn-xs btn-outline-danger"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Add / Edit Modal --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="background-color:#DC143C;">
                        <h5 class="modal-title text-white font-weight-bold">
                            <i class="fas fa-{{ $isEditing ? 'edit' : 'plus' }} mr-1"></i>
                            {{ $isEditing ? 'Edit Category' : 'Add Category' }}
                        </h5>
                        <button wire:click="$set('showModal', false)" type="button"
                                class="close text-white"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">

                        {{-- Name --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-sm">Name <span class="text-danger">*</span></label>
                            <input wire:model="name" type="text" placeholder="e.g. P1"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Type --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-sm">Type <span class="text-danger">*</span></label>
                            <select wire:model="type"
                                    class="form-control form-control-sm @error('type') is-invalid @enderror">
                                <option value="1">P (Position)</option>
                                <option value="2">LO</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Status --}}
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" wire:model="status"
                                       class="custom-control-input" id="cat_status_modal">
                                <label class="custom-control-label" for="cat_status_modal">Active</label>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button wire:click="save" type="button"
                                class="btn btn-sm font-weight-bold"
                                style="background-color:#DC143C;color:#fff;">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-1"></i> {{ $isEditing ? 'Update' : 'Save' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Saving…
                            </span>
                        </button>
                        <button wire:click="$set('showModal', false)" type="button"
                                class="btn btn-sm btn-outline-secondary">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
