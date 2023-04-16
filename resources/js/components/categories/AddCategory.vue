<template>
    <div class="Modal">
        <form @submit.prevent="addCategory">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                    >Add Center</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" required/>
            </div>
            <button>Add</button>
        </form>
    </div>
</template>

<script>
import axios from "axios";
import {mapActions} from "vuex";
import {API} from "../../api";

export default {
    emits: ["getCategories", "closeModal"],
    data() {
        return {
            name: "",
        };
    },
    methods: {
        ...mapActions(["changeLoading"]),
        addCategory() {
            this.changeLoading();
            API.addCategory({
                category_name: this.name,
            })
                .then(() => {
                    this.$emit("getCategories");
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    console.log(err);
                });
        },
        close() {
            this.$emit("closeModal")
        }
    },
};
</script>
