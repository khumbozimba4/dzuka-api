<template>
    <div class="Main__Wrap">
        <div
            style="
                display: flex;
                justify-content: space-between;
                padding:15px;
                ;
            "
        >
            <strong style="text-transform: capitalize">Add Category</strong>
            <button @click="close">Close</button>
        </div>
        <form @submit.prevent="addCategory">
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
import { mapActions } from "vuex";
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
            axios
                .post("api/categories/store", {
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
        close(){
            this.$emit("closeModal")
        }
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    border-top: 1px solid gray;
    background: #fff;
    top: 50px;
    width: 350px;
    right: 200px;
    z-index: 999;
    border-radius: 5px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);

    form {
        padding: 20px;
        h1 {
            font-weight: 800;
        }
        .Input__Container {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            input {
                background: none;
                outline: none;
                border-bottom: 1px solid gray;
                color: gray;
            }
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
