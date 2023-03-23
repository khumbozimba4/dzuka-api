<template>
    <div class="Main__Wrap">
        <div
            style="
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
            "
        >
            <strong style="text-transform: capitalize">Edit</strong>
            <button @click="close">Close</button>
        </div>
        <form @submit.prevent="editUser">
            <input type="text" v-model="name" />
            <input type="text" v-model="email" />
            <label for="role">Change Role</label>
            <select id="role" name="role" v-model="role">
                <option value="admin">Admin</option>
                <option value="operations">Operations</option>
                <option value="finance">Finance</option>
            </select>
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
            role: "",
        };
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getUser() {
            this.name = this.user.name;
            this.email = this.user.email;
            this.role = this.user.role;
        },
        editUser() {
            this.changeLoading();
            axios
                .patch(`api/users/${this.user.id}/update`, {
                    name: this.name,
                    email: this.email,
                    role: this.role,
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

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    margin-top: 10px;
    width: 35%;
    right: 10%;
    background-color: #fff;
    padding: 20px;
    z-index: 99;
    width: 300px;
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
