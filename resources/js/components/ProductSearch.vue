<template>
    <div class="Contents__Container">
        <div class="Heading">
            <h1>Search Results</h1>
            <h3 v-if="products?.length == 0">
                Oops we cant find that product for you😢
            </h3>
        </div>
        <div class="Table__Container" v-if="products?.length !== 0">
            <table class="Table">
                <thead class="Table__Head">
                    <tr class="Tr">
                        <td>#</td>
                        <td>Product name</td>
                        <td>Total Stock</td>
                        <td>Price (MWK)</td>
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
                        <td>{{ product.stock }}</td>
                        <td>{{ product.price }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ["search"],
    data() {
        return {
            errMsg: null,
            products: [],
        };
    },
    created() {
        this.searchProduct();
    },
    methods: {
        searchProduct() {
            axios
                .get(`api/products/search/${this.search}`)
                .then((res) => {
                    this.products = res.data;
                })
                .catch((err) => {
                    this.errMsg = err.message;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
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
        justify-content: space-between;
        padding: 20px;
        border-bottom: 1px solid rgb(163 163 163);
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

                    &:hover {
                        background-color: rgb(236, 236, 236);
                    }
                    .Allocate__Stock {
                        position: relative;
                        display: flex;
                        align-items: center;
                        gap: 20px;
                        .Icon {
                            height: 20px;
                            object-fit: contain;
                            cursor: pointer;
                        }
                    }
                }
            }
        }
    }
}
</style>
