<template>
    <div class="Contents__Container">
        <div class="Heading">
            <h1>Search Results</h1>
            <h3 v-if="sales?.length == 0">
                Oops we cant find that sale for you😢
            </h3>
        </div>
        <div class="Table__Container" v-if="sales?.length !== 0">
            <table class="Table">
                <thead class="Table__Head">
                    <tr class="Tr">
                        <td>SaleID</td>
                        <td>Date</td>
                        <td>Customer</td>
                        <td>Customer Contact</td>
                        <td>Product(s)</td>
                        <td>Total Price (MKW)</td>
                        <td>View Sale</td>
                    </tr>
                </thead>
                <tbody class="Table__Body">
                    <tr class="Tr" v-for="sale in sales" :key="sale.id">
                        <td>
                            <strong>{{ sale.id }}</strong>
                        </td>
                        <td>{{ sale?.date }}</td>
                        <td>{{ sale?.customer_name }}</td>
                        <td>{{ sale?.customer_contact }}</td>
                        <td>{{ sale?.products.length }}</td>
                        <td>{{ sale?.sale_amount }}</td>
                        <td>
                            <ArrowNarrowRightIcon
                                class="Icon"
                                @click="gotoSale(sale)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import { ArrowNarrowRightIcon } from "@heroicons/vue/outline";
import axios from "axios";
export default {
    props: ["search"],
    components: {
        ArrowNarrowRightIcon,
    },
    data() {
        return {
            errMsg: null,
            sales: [],
        };
    },
    created() {},
    methods: {
        gotoSale(sale) {
            this.$router.push({
                name: "sale",
                params: {
                    sale_id: sale.id,
                    customer_name: sale.customer_name,
                    customer_contact: sale.customer_contact,
                },
            });
        },
    },
    watch: {
        search(value) {
            axios
                .get(`api/sales/search/${value}`)
                .then((res) => {
                    this.sales = res.data;
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
        display: flex;
        flex-direction: column;
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
</style>
