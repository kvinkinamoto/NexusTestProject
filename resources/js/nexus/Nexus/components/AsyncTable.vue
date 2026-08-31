<template>
    <div>
        <h1>Async table</h1>
        <!-- Фільтри -->
        <form v-if="filters.length" @submit.prevent="applyFilters" class="mb-3">
            <div v-for="filter in filters" :key="filter.name" class="mb-3">
                <label :for="filter.name">{{ filter.label }}</label>
                <input :type="filter.type" :name="filter.name" :id="filter.name"
                       v-model="filterValues[filter.name]" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
        </form>

        <!-- Таблиця -->
        <table class="table table-bordered">
            <thead>
            <tr>
                <th v-for="column in columns" :key="column.name">
                    <a href="#" @click.prevent="sort(column.name)">
                        {{ column.label }}
                    </a>
                </th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in items" :key="item.id">
                <td v-for="column in columns" :key="column.name">{{ item[column.name] }}</td>
                <td>
                    <button v-for="action in actions" :key="action.name"
                            @click="performAction(action, item.id)"
                            class="btn btn-sm"
                            :class="{ 'btn-danger': action.name === 'delete', 'btn-primary': action.name !== 'delete' }">
                        <i :class="action.icon"></i> {{ action.label }}
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: ['module', 'initialData', 'columns', 'actions', 'filters'],
    data() {
        return {
            items: this.initialData?.data || [],
            filterValues: {},
            sortBy: 'id',
            sortOrder: 'desc',
        };
    },
    methods: {
        async applyFilters() {
            try {
                const response = await axios.get(`/admin/${this.module}`, {
                    params: {
                        ...this.filterValues,
                        sort_by: this.sortBy,
                        sort_order: this.sortOrder,
                    },
                });
                this.items = response.data.data;
            } catch (error) {
                console.error('Filter error:', error);
            }
        },
        async sort(column) {
            this.sortOrder = (this.sortBy === column && this.sortOrder === 'asc') ? 'desc' : 'asc';
            this.sortBy = column;
            await this.applyFilters();
        },
        async performAction(action, id) {
            if (action.confirm && !confirm('Are you sure?')) {
                return;
            }
            try {
                if (action.name === 'delete') {
                    await axios.delete(`/admin/${this.module}/${id}`);
                    this.items = this.items.filter(item => item.id !== id);
                } else if (action.name === 'edit') {
                    window.location.href = action.route.replace('{id}', id);
                } else {
                    const response = await axios.post(`/admin/${this.module}/action/${action.name}`, { id });
                    if (response.data.success) {
                        this.items = this.items.filter(item => item.id !== id);
                        alert(response.data.message);
                    }
                }
            } catch (error) {
                console.error('Action error:', error);
                alert('Action failed');
            }
        },
    },
};
</script>
