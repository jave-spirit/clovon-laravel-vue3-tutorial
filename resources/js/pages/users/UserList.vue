<script setup>
    import axios from 'axios';
    import { onMounted,ref, watch } from 'vue';
    import { Form, Field } from 'vee-validate';
    import * as yup from 'yup';
    import {useToastr} from '../../toastr.js';
    import UserListItem from './UserListItem.vue';
    import { debounce} from 'lodash';
    import { Bootstrap4Pagination } from 'laravel-vue-pagination';
    
   
    const users = ref({'data': []});
    const editing = ref(false);
    const formValues = ref();
    const form = ref(null);
    const toastr = useToastr();
    const userIdbeingDeleted = ref(null);

        const confirmUserdeletion = (id) => {
                userIdbeingDeleted.value = id;
                $(deleteUserModal).modal('show');
                
                
            };

        const deleteUser = () => {
            axios.delete(`/api/users/${userIdbeingDeleted.value}`)
            .then(()=>{
                $(deleteUserModal).modal('hide');
                toastr.success('User was deleted successfully!');
                users.value.data =users.value.data.filter(user => user.id !== userIdbeingDeleted.value);
            });
        }



        const clearForm = () => {
            form.value.resetForm();
            formValues.value = {
                name: '',
                email: '',
                password: '',
            };
            
        }
   

         const getUsers = (page = 1) => {
            
            axios.get(`/api/users?page=${page}`)
            .then((response) => {
                    users.value = response.data;
                    selectedUsers.value = [];
                    selectAll.value = false;
                })
            }

        const createUser = (values, {resetForm, setErrors}) => {
                axios.post('/api/users', values)
                 .then((response) => {
                users.value.data.unshift(response.data);
                $('#UserFormModal').modal('hide');
               resetForm();
               toastr.success('Added Successfully!');
            })
                .catch((error)=>{
                    if(error.response.data.errors){
                        setErrors(error.response.data.errors);
                    }
                });
            };

        const createUserSchema = yup.object({
            name: yup.string().required(),
            email:yup.string().email().required(),
            password:yup.string().required().min(8),
            
        });

        const editUserSchema = yup.object({
            name: yup.string().required(),
            email: yup.string().email().required(),
            password: yup.string().notRequired().test('password', 'Passwords must be be minimum of 8 characters', function(value) {
                if (!!value) {
                const schema = yup.string().min(8);
                return schema.isValidSync(value);
                }
                return true;
            }),
        });


        const addUser = () => {
            editing.value = false;
            form.value.resetForm();
            formValues.value = {
                name: '',
                email: '',
                password: '',
            };
            $('#UserFormModal').modal('show');
        }; 

        const editUser = (user) => {
            editing.value = true;
            $('#UserFormModal').modal('show');
            formValues.value = {
                id: user.id,
                name: user.name,
                email: user.email,
            };
            
        };

        const updateUser = (values, {setErrors}) => {
            axios.put('/api/users/'+ formValues.value.id, values)
            .then((response) =>{
                const index = users.value.data.findIndex(user => user.id === response.data.id);
                users.value.data[index] = response.data;
                $('#UserFormModal').modal('hide');
                form.value.resetForm();
                toastr.success('Updated Successfully!');
            }).catch((error) => {
                setErrors(error.response.data.errors);
                console.log(error);
            });
        }
        const handleSubmit = (values, actions) => {
            /* console.log(actions); */
            if(editing.value){
                updateUser(values, actions);
            }else{
                createUser(values, actions);
            }
        }

        const searchQuery = ref(null);

        const search = () => {
            axios.get('/api/users/search',{
                params: {
                    query: searchQuery.value
                }
            })
            .then(response => {
                users.value = response.data;
            })
            .catch(error =>{
                console.log(error);
            })
        };
        watch(searchQuery,debounce(()=>{
            search();
        },300));


        const selectedUsers = ref([]);
        const toggleSelection = (user) =>{

            const index = selectedUsers.value.indexOf(user.id);
            if(index === -1){
                selectedUsers.value.push(user.id);
            }
            else{
                selectedUsers.value.splice(index,1);
            }
            
            console.log(selectedUsers.value);
        };

        const bulkDelete = () => {
            axios.delete('/api/users', {
                data:{
                    ids: selectedUsers.value
                }
            })
            .then (response => {
                users.value.data = users.value.data.filter(user => !selectedUsers.value.includes(user.id));
                selectedUsers.value = [];
                selectAll.value = false;
                toastr.success(response.data.message);
            })
        };

        const selectAll = ref(false);
        const selectAllUsers = () => {
            if(selectAll.value){
                selectedUsers.value = users.value.data.map(user => user.id);
            }else{
                selectedUsers.value=[];
            }
        }

    onMounted(() => {
        getUsers();
    });
</script>

<template>
    <div class="content-header">
         <div class="container-fluid">
             <div class="row mb-2">
                 <div class="col-sm-6">
                     <h1 class="m-0">Users</h1>
                 </div>
                 <div class="col-sm-6">
                     <ol class="breadcrumb float-sm-right">
                         <li class="breadcrumb-item"><router-link to="/admin/dashboard">Home</router-link></li>
                         <li class="breadcrumb-item active">Users</li>
                     </ol>
                 </div>
             </div>
         </div>
    </div>
 
 
    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between" >

                <div class="d-flex">
                    <button @click="addUser()" type="button" class="mb-2 btn btn-primary" data-toggle="modal" data-target="#UserFormModal">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New User
                    </button>
                    <div>
                        <button v-if="selectedUsers.length> 0" @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                         <i class="fa fa-trash mr-1"></i>
                         Delete Selected 
                        </button> 
                         <span class="badge ml-2 badge-pill badge-primary" v-if="selectedUsers.length > 0 | selectAll > 0" >Selected {{ selectedUsers.length }} User</span>
                    </div>

                

                </div>
                <!-- <div>
                    <input type="search" v-model="searchQuery" class="form=control rounded" placeholder="Search..."/>
                </div> -->
                <div class="d-flex justify-content-between">
                    <div class="input-container">
                        
                        <input type="search"  v-model="searchQuery" class="input-field" placeholder="Search..."/>
                        <i class="fas fa-search icon"></i>
                         
                    </div>
                    
                        
                    
                </div>
                

        </div>
             <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input v-model="selectAll" type="checkbox" @change="selectAllUsers"/></th>
                                <th style="width: 10px">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Role</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody v-if="users.data.length > 0">
                            <UserListItem v-for="(user, index) in users.data"
                                :key="user.id"
                                :user=user
                                :index=index
                               @edit-user = "editUser"
                               @confirm-user-deletion = "confirmUserdeletion"
                               @toggle-selection = "toggleSelection"
                               :select-all="selectAll"
                            />
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="text-center">No results found...</td>
                            </tr>
                        </tbody>
                    </table>
                   
                </div>
             </div>
             <Bootstrap4Pagination :data="users" @pagination-change-page="getUsers" />
         </div>
        </div>

       
            <div class="modal fade" id="UserFormModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel"  aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">
                                <span v-if="editing">Edit User</span>
                                 <span v-else>Add New User</span>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                            <Form  ref="form" @submit="handleSubmit" :validation-schema="editing ? editUserSchema : createUserSchema" v-slot="{ errors }" :initial-values="formValues">
                                <div class="modal-body">
                                    
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <Field name="name" type="text" class="form-control" :class="{'is-invalid': errors.name}" id="name" aria-describedby="nameHelp" placeholder="Enter fullname" />
                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                            </div>
                                            <div class="form-group">
                                                <label for="name">Email</label>
                                                <Field name="email" type="email" class="form-control" :class="{'is-invalid': errors.email}" id="email" aria-describedby="nameHelp" placeholder="Enter email" />
                                                <span class="invalid-feedback">{{ errors.email }}</span>
                                            </div>
                                            <div class="form-group">
                                                <label for="password">Password</label>
                                                <Field name="password" type="password" class="form-control" :class="{'is-invalid': errors.password}" id="password" aria-describedby="nameHelp" placeholder="Enter password" />
                                                <span class="invalid-feedback">{{ errors.password }}</span>
                                            </div>
                                    
                                </div>  
                                <div class="modal-footer">
                                    <button type="button" @click="clearForm" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </Form>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="deleteUserModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel"  aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">
                                <span>Delete User</span>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                            <div class="modal-body">
                                <h5>Are you sure about deleting this user?</h5> 
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button @click.prevent="deleteUser()" type="button" class="btn btn-primary">Delete User</button>
                            </div>
                    </div>
                </div>
            </div>
 </template>

<style>
.input-container {
  display: flex;
  width: 100%;
  margin-bottom: 15px;
  
}

.icon {
  padding: 10px;
  background: rgb(82, 106, 242);
  color: rgb(244, 250, 247);
  min-width: 50px;
  text-align: center;
  border-radius: 0px 5px 5px 0px;
}

/* Style the input fields */
.input-field {
  width: 100%;
  outline: none;
  border-radius: 5px 0px 0px 5px;
}

.input-field:focus {
  border: 2px solid rgb(114, 134, 242);
}



</style>




