<template>
    <div class="Main__Wrapper">
        <div class="NavBar__Container">
            <div class="Title">
                <ShoppingBagIcon class="Icon"/>
                <p>Supplies</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search..."
                    v-model="search"
                />
                <SearchIcon class="Search__Icon"/>
            </div>

            <div class="Options"></div>
        </div>
        <div v-if="errorMessage">{{ errorMessage }}</div>
        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon"/>
                    Filters
                </div>
                <div class="Right__Side">
                    <PrinterIcon class="Icon"/>
                </div>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                    <tr class="Tr">
                        <td>Date</td>
                        <td>Product</td>
                        <td>Quantity</td>
                        <td>Supplier</td>
                    </tr>
                    </thead>
                    <tbody class="Table__Body">
                    <tr
                        class="Tr"
                        v-for="supple in supplies"
                        :key="supple.id"
                    >
                        <td>{{ getDate(supple.created_at) }}</td>
                        <td>{{ supple.product.product_name }}</td>
                        <td>{{ supple.quantity }}</td>
                        <td>{{ supple.supplier.name }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="p-4" v-if="supplies.length == 0">
                    No Supplies made yet
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import moment from "moment";
import {
    AdjustmentsIcon,
    CreditCardIcon,
    PrinterIcon,
    SearchIcon, ShoppingBagIcon
} from "@heroicons/vue/outline";

export default {
    name: "Audits",
    components: {
        SearchIcon,
        AdjustmentsIcon,
        PrinterIcon,
        CreditCardIcon,
        ShoppingBagIcon
    },
    data() {
        return {
            supplies: [],
            errorMessage: ""
        }
    },
    created() {
        this.getAuditSubmissions()
    },
    methods: {
        getAuditSubmissions() {
            axios.get("api/add-inventory")
                .then(({data}) => {
                    this.supplies = data;
                })
                .catch(({message}) => {
                    this.errorMessage = message
                })
        },
        getDate(date) {
            return moment(new Date(date)).format("LL");
        },
    }
}
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
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid rgb(163 163 163);

            .Left__Side {
                display: flex;
                gap: 10px;
                align-items: center;
                font-weight: 700;
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

                        &:hover {
                            background-color: rgb(236, 236, 236);
                        }

                        .Icons {
                            display: flex;
                            gap: 30px;
                        }

                        td {
                            .Icon {
                                width: 25px;
                                object-fit: contain;
                                cursor: pointer;
                                color: rgb(23, 34, 49);

                                &:hover {
                                    color: rgb(2, 2, 3);
                                }
                            }

                            .Icon_Delete {
                                color: rgb(209, 74, 74);

                                &:hover {
                                    color: rgb(155, 23, 23);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
