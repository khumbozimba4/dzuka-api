<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <p><strong>SaleID</strong></p>
            </div>

            <div class="Options">
                <strong>{{ sale_id }}</strong>
            </div>
        </div>

        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <p><strong>Customer</strong> : {{ customer_name }}</p>
                    <p><strong>Total Amount</strong> : K{{ totalAmount }}</p>
                </div>
                <div class="Right__Side">
                    <div class="Add__Category" @click="isOpen = !isOpen">
                        Add Products
                    </div>
                </div>
            </div>

            <div class="Table__Container" v-if="!errorMessage">
                <table class="Table">
                    <thead class="Table__Head">
                        <tr class="Tr">
                            <td>#</td>
                            <td>Product</td>
                            <td>Price</td>
                            <td>Quantity</td>
                            <td>Total Price</td>
                        </tr>
                    </thead>
                    <tbody class="Table__Body">
                        <tr
                            class="Tr"
                            v-for="(product, index) in products"
                            :key="product.id"
                        >
                            <td>
                                <strong>{{ index + 1 }}</strong>
                            </td>
                            <td>{{ product.product_name }}</td>
                            <td>K{{ product.price }}</td>
                            <td>{{ product.pivot.quantity }}</td>
                            <td>
                                K{{ product.price * product.pivot.quantity }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else>{{ errorMessage }}</div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            products: [],
            isOpen: false,
            sale_id: null,
            customer_name: null,
            customer_contact: null,
            totalAmount: 0,
            errorMessage: null,
        };
    },
    created() {
        this.getProducts();
    },
    methods: {
        getProducts() {
            this.sale_id = this.$route.params.sale_id;
            this.customer_name = this.$route.params.customer_name;
            this.customer_contact = this.$route.params.customer_contact;
            axios
                .get(`api/sales/${this.sale_id}/products`)
                .then((res) => [(this.products = res.data)])
                .then(() => {
                    this.getAmount();
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        getAmount() {
            for (let i = 0; i < this.products.length; i++) {
                this.totalAmount +=
                    this.products[i].price * this.products[i].pivot.quantity;
            }
        },
    },
};
</script>

<style lang="scss" scoped>
.Main__Wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    .NavBar__Container {
        background-color: #fff;
        display: flex;
        align-items: center;
        width: 100%;
        padding: 10px;

        .Title {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid gray;
            margin-right: 25px;
            .Icon {
                height: 30px;
                object-fit: contain;
            }
        }

        .Search__Bar {
            display: flex;
            align-items: center;
            background-color: rgb(212 212 212);
            border-radius: 5px;
            .Input {
                background: none;
                border: 0px;
                padding: 10px 20px;
                width: 200px;

                &:focus {
                    outline: none;
                    border: 0px;
                }
            }
            .Search__Icon {
                padding: 5px 20px;
                height: 30px;
                color: rgb(115 115 115);
            }
        }
    }
    .Contents__Container {
        margin: 20px;
        background-color: #fff;
        border-radius: 3px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
            0 4px 6px -4px rgb(0 0 0 / 0.1);
        .Heading {
            position: relative;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);
            .Left__Side {
                display: flex;
                flex-direction: column;
                gap: 5px;
                cursor: pointer;

                .Icon {
                    height: 20px;
                    object-fit: contain;
                    cursor: pointer;
                }
            }
            .Right__Side {
                display: flex;
                align-items: center;
                gap: 10px;
                .Add__Category {
                    padding: 5px 20px;
                    border: 1px solid rgb(115 115 115);
                    border-radius: 3px;
                    color: rgb(115 115 115);
                    cursor: pointer;
                    font-size: 15px;

                    &:hover {
                        color: rgb(82 82 82);
                    }
                }
                .Icon {
                    height: 30px;
                    object-fit: contain;
                    padding: 5px 20px;
                    border: 1px solid rgb(115 115 115);
                    border-radius: 3px;
                    color: rgb(115 115 115);
                    cursor: pointer;
                }
            }
        }
        .Table__Container {
            padding: 20px;
            .Table {
                width: 100%;

                .Table__Head {
                    font-weight: 800;
                    color: rgb(38 38 38);
                    .Tr {
                        height: 40px;
                    }
                }
                .Table__Body {
                    .Tr {
                        border-top: 1px solid rgb(229 229 229);
                        height: 40px;
                        td {
                            .Icon {
                                height: 30px;
                                object-fit: contain;
                                cursor: pointer;
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
