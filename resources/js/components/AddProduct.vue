<template>
    <div class="Main__Wrap">
        <form @submit.prevent="addProduct">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>
                    <strong style="text-transform: capitalize"
                        >Add Product</strong
                    >
                </h1>
                <button @click="close">Close</button>
            </div>
            <div class="Input__Container">
                <label for="name">Name</label>
                <input name="name" v-model="name" required/>
            </div>
            <div class="Input__Container">
                <label for="price">Price (MWK)</label>
                <input name="price" type="number" v-model="price" required/>
            </div>
            <div class="Input__Container">
                <label for="description">Description (Optional)</label>
                <textarea
                    id="w3review"
                    name="description"
                    v-model="description"
                    rows="4"
                    cols="30"
                />
            </div>
            <div class="File_Input">
                <label for="product_photo">Product Photo</label>
                <input type="file" id="product_photo" ref="product_photo" class="custom-file-input" @change="previewFiles" accept="image/*">
            </div>
            <button>Add</button>

            <div v-if="errorMessage">{{ errorMessage }}</div>
        </form>
    </div>
</template>

<script>
import axios from "axios";
import { mapActions } from "vuex";
export default {
    emits: ["getProducts", "closeModal"],
    props: ["category_id"],
    data() {
        return {
            description: "",
            name: "",
            price: null,
            errorMessage: null,
            product_photo:'',
        };
    },
    methods: {
        ...mapActions(["changeLoading"]),
        addProduct() {
            this.changeLoading();
            let formData = new FormData();
            formData.append('product_photo', this.product_photo);
            formData.append('product_name', this.name);
            formData.append('description', this.description);
            formData.append('price', this.price);
            formData.append('category_id', this.category_id);
            console.log(formData)
            axios
                .post("api/products/store", formData, {
                    headers:{
                        "Content-Type": "multipart/form-data"
                    }
                })
                .then(() => {
                    this.$emit("closeModal");
                })
                .then(() => {
                    this.$emit("getProducts");
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.changeLoading();
                    this.errorMessage = err.message;
                });
        },
        close(){
            this.$emit("closeModal");
        },
        previewFiles(){
            this.product_photo = this.$refs.product_photo.files[0]
            console.log(this.product_photo);
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
        .File_Input{
            display: flex; flex-direction: column; gap: 5px; margin-top: 10px
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
