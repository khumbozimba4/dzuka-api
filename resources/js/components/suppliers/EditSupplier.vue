<template>
    <div class="Main__Wrap">
        <form @submit.prevent="editSupplier">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                    >Add Supplier</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" required/>
            </div>
            <div class="Input__Container">
                <label for="phone_number">Phone Number</label>
                <input name="phone_number" type="number" v-model="phone_number" required/>
            </div>
            <div class="Input__Container">
                <label for="location">Location</label>
                <input name="location" v-model="location" required/>
            </div>
            <button>Add</button>

            <div v-if="errorMessage">{{ errorMessage }}</div>
        </form>
    </div>
</template>

<script>
import axios from "axios";
import {mapActions} from "vuex";
export default {
    name: "EditSupplier",
    emits: ["getSuppliers", "closeModal"],
    props:["supplier"],
    data() {
        return {
            name: "",
            location: "",
            phone_number: null,
            errorMessage: "",
        };
    },
    created() {
        this.getSupplier()
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getSupplier() {
            this.name = this.supplier.name;
            this.phone_number = this.supplier.phone_number;
            this.location = this.supplier.location;
        },
        editSupplier() {
            axios
                .patch("api/suppliers", {
                    name: this.name,
                    location: this.location,
                    phone_number: this.phone_number
                })
                .then(() => {
                    this.$emit("closeModal");
                })
                .then(() => {
                    this.$emit("getSuppliers");
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        close() {
            this.$emit("closeModal");
        }
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrap {
    position: absolute;
    border-top: 1px solid gray;
    background: #fff;
    z-index: 999;
    top: 50px;
    right: 200px;
    border-radius: 5px;
    width: 500px;
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

        .File_Input {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-top: 10px
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
