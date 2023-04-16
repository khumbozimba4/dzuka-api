<template>
    <div class="Modal">
        <form @submit.prevent="editCategory">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                    >Edit Center</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" required/>
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script>
import { mapActions } from "vuex";
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
        ...mapActions(["changeLoading"]),
        getCategory() {
            this.name = this.category.category_name;
            this.description = this.category.description;
        },
        editCategory() {
            this.changeLoading();
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
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errMsg = err.message;
                });
        },
        close() {
            this.$emit("closeModal");
        },
    },
};
</script>
