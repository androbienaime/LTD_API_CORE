import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";

export const fetchTodo = createAsyncThunk("fetchTodo", async ()=>{
    const data = await fetch("http://127.0.0.1:8000/api/list_product");
    return data.json()
})

const todoSlice = createSlice({
    name: 'todo',
    initialState: {
        isLoading: false,
        data: [],
        error: false
    },
    extraReducers : (builder) => {
        builder.addCase(fetchTodo.pending, (state, action) =>{
            state.isLoading = true
        })

        builder.addCase(fetchTodo.fulfilled, (state, action) =>{
            state.isLoading = false,
            state.data = action.payload
        })

        builder.addCase(fetchTodo.rejected, (state, action) =>{
            state.error = true
        })
    }
})

export default todoSlice.reducer;