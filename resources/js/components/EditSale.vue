<template>
    <div class="Main__Wrap">
        <form @submit.prevent="editSale">
            <input type="text" v-model="name" />
            <input type="text" v-model="contact" />
            <input type="date" v-model="date" />
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
import { mapActions } from "vuex";
export default {
    props: ["sale"],
    emits: ["getSales", "closeModal"],
    created() {
        this.getCategory();
    },
    data() {
        return {
            name: "",
            contact: "",
            date: null,
            description: "",
        };
    },
    methods: {
        ...mapActions(["changeLoading"]),

        getCategory() {
            this.name = this.sale.customer_name;
            this.description = this.sale.description;
            this.contact = this.sale.customer_contact;
            this.date = this.sale.date;
        },
        editSale() {
            this.changeLoading();
            axios
                .patch(`api/sales/${this.sale.id}/update`, {
                    customer_name: this.name,
                    description: this.description,
                    customer_contact: this.contact,
                    date: this.date,
                })
                .then(() => {
                    this.$emit("closeModal");
                })
                .then(() => {
                    this.$emit("getSales");
                })
                .then(() => {
                    this.changeLoading();
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
