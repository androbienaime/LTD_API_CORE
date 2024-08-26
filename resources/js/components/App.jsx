import React from 'react';
import ReactDOM from 'react-dom/client';
import AOS from "aos";
import "aos/dist/aos.css";
import { BrowserRouter, createBrowserRouter, createRoutesFromElements, Route, RouterProvider, Routes } from 'react-router-dom';
import Layout from './Layout/Layout';
import Home from './screen/Home/Home';

const router = createBrowserRouter(
    createRoutesFromElements(
        <Route>
            <Route path='/' element={ <Layout /> }>
              <Route index element={ <Home/> }></Route>
            </Route>
        </Route>
    )
)

function App() {
    React.useEffect(() => {
        AOS.init({
            offset: 100,
            duration: 800,
            easing: "ease-in-out",
            delay:0
        })
    }, []);

    return (
        <div >
            <RouterProvider router={ router } />
        </div>
    );
}


export default App;

