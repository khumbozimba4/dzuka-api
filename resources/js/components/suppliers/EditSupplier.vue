<template>
    <div class="Main__Wrap">
        <form @submit.prevent="editSupplier">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                    >Edit Supplier</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" required/>
            </div>
            <div class="Input__Container">
                <label for="location">Location</label>
                <input name="location" v-model="location" required/>
            </div>
            <div class="Input__Container">
                <label for="phone_number">Phone Number</label>
                <input name="phone_number" type="number" v-model="phone_number" required/>
            </div>
            <div class="Input__Container">
                <label for="pin">Pin (4 digits)</label>
                <input name="pin" type="number" v-model="pin" required/>
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
    props: ["supplier"],
    data() {
        return {
            name: "",
            location: "",
            phone_number: null,
            pin: null,
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
            this.pin = this.supplier.pin;
        },
        editSupplier() {
            this.changeLoading();
            axios
                .patch(`api/suppliers/${this.supplier.id}`, {
                    name: this.name,
                    location: this.location,
                    phone_number: this.phone_number,
                    pin: this.pin
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
    z-index: 9999;
    position: absolute;
    width: 100vw;
    height: 100vh;
    top: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.5);
    display: grid;
    place-items: center;

    form {
        width: 50%;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -4px rgb(0 0 0 / 0.1);
        background: #fff;
        padding: 20px;
        border-radius: 10px;

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
