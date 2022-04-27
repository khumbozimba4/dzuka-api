<template>
    <div class="Main__Wrap">
        <form @submit.prevent="editCategory">
            <input type="text" v-model="name" />
            <textarea
                id="w3review"
                name="description"
                v-model="description"
                rows="4"
                cols="30"
            ></textarea>
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script>
export default {
    props: ["category"],
    emits: ["getCategories", "closeModal"],
    created() {
        this.getCategory();
    },
    data() {
        return {
            name: "",
            description: "",
        };
    },
    methods: {
        getCategory() {
            this.name = this.category.category_name;
            this.description = this.category.description;
        },
        editCategory() {
            axios
                .patch(`api/categories/${this.category.id}/update`, {
                    category_name: this.name,
                    description: this.description,
                })
                .then(() => {
                    this.$emit("closeModal");
                })
                .then(() => {
                    this.$emit("getCategories");
                })
                .catch((err) => {
                    this.errMsg = err.message;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    top: 20px;
    right: 100px;
    background-color: #fff;
    padding: 20px;
    z-index: 99;
    form {
        display: flex;
        flex-direction: column;
        gap: 10px;

        input {
            width: 100%;
            padding: 10px;
        }
        button {
            padding: 5px 10px;
            background-color: rgb(30 41 59);
            color: #fff;
            border-radius: 3px;
            margin-top: 10px;

            &:hover {
                background: rgb(15 23 42);
            }
        }
    }
}
</style>
