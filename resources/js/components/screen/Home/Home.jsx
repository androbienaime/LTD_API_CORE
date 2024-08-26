import React, { useEffect } from "react";
import Hero from "../../Layout/Hero/Hero";
import Products from "../../Layout/Products/Products";
import ListProducts from "../../Layout/Products/ListProducts";
import FeaturedProduct from "../../Layout/Featured/FeaturedProduct";
const Home = () =>{
    
    return (
        <div>
            <Hero/>
            <FeaturedProduct />
            <ListProducts/>
        </div>
    )
}

export default Home;