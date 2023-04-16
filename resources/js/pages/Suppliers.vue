<template>
    <div class="Main__Wrapper">
        <ConfirmDelete
            @toggleCancel="confirmDelete = false"
            @toggleDelete="deleteSupplier"
            v-if="confirmDelete"
        />
        <div class="NavBar__Container">
            <div class="Title">
                <UserGroupIcon class="Icon"/>
                <p>Suppliers</p>
            </div>
            <div class="Search__Bar">
                <input
                    type="text"
                    class="Input"
                    placeholder="Search Suppliers"
                    v-model="search"
                />
                <SearchIcon class="Search__Icon"/>
            </div>
            <div class="Options"></div>
        </div>
        <CategorySearch v-if="search" :search="search"/>
        <div class="Contents__Container">
            <div class="Heading">
                <div class="Left__Side">
                    <AdjustmentsIcon class="Icon"/>
                    Filters
                </div>
                <div class="Right__Side">
                    <div
                        class="Add__Category"
                        @click="addSupplier = !addSupplier"
                        v-if="userInfo.role !== finance"
                    >
                        Add Supplier
                    </div>
                    <PrinterIcon class="Icon"/>
                </div>
                <AddSupplier @getSuppliers="getSuppliers" v-if="addSupplier" @closeModal="addSupplier = false"/>
            </div>
            <div class="Table__Container">
                <table class="Table">
                    <thead class="Table__Head">
                    <tr class="Tr">
                        <td>#</td>
                        <td>Supplier Name</td>
                        <td>Phone Number</td>
                        <td>Location</td>
                        <td v-if="userInfo.role !== finance">Actions</td>
                    </tr>
                    </thead>
                    <tbody class="Table__Body">
                    <tr v-if="!suppliers.length">
                        No suppliers available!
                    </tr>
                    <tr
                        class="Tr"
                        v-for="(supplier, index) in suppliers"
                        :key="supplier.id"
                    >
                        <td>
                            <strong>{{ index + 1 }}</strong>
                        </td>
                        <td>{{ supplier.name }}</td>
                        <td>{{ getPhoneNumber(`${supplier.phone_number}`) }}</td>
                        <td>{{ supplier.location }}</td>
                        <td class="Icons" v-if="userInfo.role !== finance">
                            <PencilIcon
                                class="Icon"
                                @click="toggleEditSupplier(supplier)"
                            />
                            <TrashIcon
                                class="Icon Icon_Delete"
                                @click="toggleDeleteSupplier(supplier.id)"
                            />
                            <EditSupplier
                                :supplier="supplier"
                                @getSuppliers="getSuppliers"
                                @closeModal="editSupplier = false"
                                v-if=" editSupplier && selected === suppliers[index]"
                            />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import {
    AdjustmentsIcon,
    ArrowNarrowRightIcon,
    CollectionIcon,
    PencilIcon,
    PrinterIcon,
    SearchIcon,
    TrashIcon,
    UserGroupIcon
} from "@heroicons/vue/outline";
import {mapActions, mapGetters} from "vuex";
import AddSupplier from "../components/suppliers/AddSupplier";
import EditSupplier from "../components/suppliers/EditSupplier";
import ConfirmDelete from "../components/ConfirmDelete";
import {PhoneNumberFormatter} from "../factories/PhoneNumberFormatterFactory";

export default {
    name: "Suppliers",
    components: {
        EditSupplier,
        AddSupplier,
        ConfirmDelete,
        SearchIcon,
        TrashIcon,
        PencilIcon,
        CollectionIcon,
        AdjustmentsIcon,
        PrinterIcon,
        ArrowNarrowRightIcon,
        UserGroupIcon
    },
    data() {
        return {
            'suppliers': [],
            'addSupplier': false,
            'editSupplier': false,
            'selected': null,
            'confirmDelete': false,
            'deletedItem': null,
            'errorMessage': ""
        }
    },
    created() {
        this.getSuppliers();
    },
    computed: {
        ...mapGetters(["userInfo"]),
    },
    methods: {
        ...mapActions(["changeLoading"]),
        getSuppliers() {
            axios.get('api/suppliers')
                .then(({data}) => {
                    this.suppliers = data
                })
                .catch((error) => {
                    console.log(error.message)
                })
        },
        toggleDeleteSupplier(id) {
            this.deletedItem = id;
            this.confirmDelete = true;
        },
        toggleEditSupplier(id) {
            this.editSupplier = true;
            this.selected = id;
        },
        deleteSupplier() {
            this.changeLoading();
            axios
                .delete(`api/suppliers/${this.deletedItem}`)
                .then(() => {
                    this.confirmDelete = false;
                })
                .then(() => {
                    this.getSuppliers();
                })
                .then(() => {
                    this.changeLoading();
                })
                .catch((err) => {
                    this.errorMessage = err.message;
                });
        },
        getPhoneNumber(phone) {
            return PhoneNumberFormatter.getPhoneNumber(phone);
        }
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

