<template>
    <div class="Modal">
        <form @submit.prevent="editUser">
            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                "
            >
                <strong style="text-transform: capitalize">Edit User</strong>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" type="text" v-model="name" required />
            </div>
            <div class="Input__Container">
                <label for="name">Email</label>
                <input name="email" type="email" v-model="email" required />
            </div>
            <div class="Input__Container">
                <label for="role_id">Change Role</label>
                <select
                    id="role_id"
                    name="role_id"
                    v-model="role_id"
                    style="padding: 10px"
                >
                    <option value="1">Admin</option>
                    <option value="2">Operations</option>
                    <option value="3">Finance</option>
                </select>
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script>
import axios from "axios";
import { mapActions } from "vuex";
export default {
    props: ["user"],
    emits: ["getUsers", "closeModal"],
    created() {
        this.getUser();
    },
    data() {
        return {
            name: "",
            email: "",
            role_id: "",
            role: "",
        };
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getUser() {
            this.name = this.user.name;
            this.email = this.user.email;
            this.role = this.user.role.name;
            this.role_id = this.user.role_id;
        },
        editUser() {
            this.changeLoading();
            axios
                .patch(`api/users/${this.user.id}`, {
                    name: this.name,
                    email: this.email,
                    role_id: this.role_id,
                })
                .then(() => {
                    this.$emit("closeModal");
                })
                .then(() => {
                    this.$emit("getUsers");
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
