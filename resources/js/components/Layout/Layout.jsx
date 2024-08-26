import React from "react";
import Header from "./Header/Header";
import Navbar from "./Navbar/Navbar";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { Outlet } from "react-router-dom";

const Layout = ({ children }) => {
    return (
        <div className="relative">
            <ToastContainer
                position="top-right"
                autoClose={1000}
                hideProgressBar={false}
                newestOnTop={false}
                closeOnClick
                rtl={false}
                pauseOnFocusLoss
                draggable
                pauseOnHover
                theme="colored"
            />
            <Header/>
            <Navbar/>
            <div className="z-40"><Outlet/></div>
        </div>
    )
}

export default Layout;
