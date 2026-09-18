<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <?php echo e($workspace->name); ?>

                </h2>
                <?php if($userRole === 'owner'): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        Owner
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                        Member
                    </span>
                <?php endif; ?>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $workspace)): ?>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('workspaces.edit', $workspace)); ?>" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md font-medium text-xs hover:bg-gray-200 transition">
                        Edit Workspace
                    </a>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $workspace)): ?>
                        <form action="<?php echo e(route('workspaces.destroy', $workspace)); ?>" method="POST" onsubmit="return confirm('Hapus workspace ini beserta seluruh project dan task di dalamnya?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md font-medium text-xs hover:bg-red-200 transition">
                                Hapus Workspace
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <?php if(session('success')): ?>
                <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <!-- Section 1: Dashboard Projects -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Project dalam Workspace</h3>
                        <p class="text-sm text-gray-500">Pantau progress pengerjaan tugas di setiap project.</p>
                    </div>

                    <a href="<?php echo e(route('workspaces.projects.create', $workspace)); ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        + Buat Project Baru
                    </a>
                </div>

                <?php if($workspace->projects->isEmpty()): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                        Belum ada project di workspace ini. Klik "+ Buat Project Baru" untuk memulai.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php $__currentLoopData = $workspace->projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $progress = $project->progressPercentage();
                                $totalTasks = $project->tasks->count();
                                $doneTasks = $project->tasks->where('status.value', 'done')->count() + $project->tasks->where('status', 'done')->count();
                            ?>
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-gray-900 text-lg">
                                            <a href="<?php echo e(route('workspaces.projects.show', [$workspace, $project])); ?>" class="hover:text-indigo-600">
                                                <?php echo e($project->name); ?>

                                            </a>
                                        </h4>
                                    </div>
                                    <?php if($project->description): ?>
                                        <p class="text-xs text-gray-600 line-clamp-2"><?php echo e($project->description); ?></p>
                                    <?php endif; ?>

                                    <?php if($project->deadline): ?>
                                        <p class="text-xs text-gray-400">
                                            📅 Deadline: <span class="font-medium text-gray-600"><?php echo e($project->deadline->format('d M Y')); ?></span>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Progress Bar -->
                                    <div class="space-y-1.5 pt-2">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-medium text-gray-600">Progress</span>
                                            <span class="font-bold text-indigo-600"><?php echo e($progress); ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: <?php echo e($progress); ?>%"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 mt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                                    <span class="text-gray-500">Task: <strong class="text-gray-800"><?php echo e($doneTasks); ?>/<?php echo e($totalTasks); ?></strong> selesai</span>
                                    <a href="<?php echo e(route('workspaces.projects.show', [$workspace, $project])); ?>" class="font-semibold text-indigo-600 hover:text-indigo-900">
                                        Lihat Task &rarr;
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section 2: Anggota Workspace -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Anggota Workspace</h3>
                        <p class="text-sm text-gray-500">Anggota yang dapat mengakses dan mengerjakan project di sini.</p>
                    </div>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inviteMember', $workspace)): ?>
                        <form action="<?php echo e(route('workspaces.members.invite', $workspace)); ?>" method="POST" class="flex gap-2">
                            <?php echo csrf_field(); ?>
                            <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['name' => 'email','type' => 'email','placeholder' => 'Email anggota baru...','required' => true,'class' => 'text-sm px-3 py-1.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','type' => 'email','placeholder' => 'Email anggota baru...','required' => true,'class' => 'text-sm px-3 py-1.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-xs']); ?>+ Undang <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role Workspace</th>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('removeMember', $workspace)): ?>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php $__currentLoopData = $workspace->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        <?php echo e($member->name); ?>

                                        <?php if($member->id === auth()->id()): ?>
                                            <span class="text-xs text-indigo-600 font-semibold">(Anda)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">
                                        <?php echo e($member->email); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($member->pivot->role === 'owner'): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                Owner
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                                Member
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('removeMember', $workspace)): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <?php if($member->pivot->role !== 'owner'): ?>
                                                <form action="<?php echo e(route('workspaces.members.remove', [$workspace, $member])); ?>" method="POST" class="inline" onsubmit="return confirm('Keluarkan anggota ini dari workspace?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Keluarkan</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\OneDrive\Documents\Praktikum2PPK\resources\views/workspaces/show.blade.php ENDPATH**/ ?>