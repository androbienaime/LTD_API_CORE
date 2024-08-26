import React from "react";
import { Swiper, SwiperSlide } from 'swiper/react';
import { Pagination, Navigation, Autoplay } from 'swiper/modules';
import { EffectFade } from 'swiper/modules';

import 'swiper/css/effect-fade';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import image1 from "../../../assets/img/1.jpg";
import image2 from "../../../assets/img/2.jpg";
import image3 from "../../../assets/img/3.jpg";
import image4 from "../../../assets/img/4.jpg";

const ImgSlide = [
    {
        id : "1",
        src: image1
    }, 
    {
        id: "2",
        src: image2
    },
    {
        id: "3",
        src: image3,
    },
    {
        id: "4",
        src: image4
    }
];
const Hero = () => {
    return (
        <div className="relative overflow-hidden max-h-[550px] sm:max-h[650px] bg-gray-100 flex">
             <div className="container pb-8 sm:pb-8 absolute">
                <div>
                    <div className="flex grid grid-cols-1 sm:grid-cols-2">
                        <h1 className="text-2xl">Le temps avec les heros</h1>
                    </div>
                </div>
             </div>
             
             <Swiper
                slidesPerView={1}
                spaceBetween={30}
                loop={true}
                autoplay={true}
                effect="fade"
                pagination={{
                clickable: true,
                }}
                navigation={true}
                modules={[Pagination, Navigation, Autoplay, EffectFade]}
                className="mySwiper"
            >
                {ImgSlide.map((el)=>{
                  return <SwiperSlide key={el.id}><img width="100%" src={el.src} alt={el.alt} /></SwiperSlide>
                })}
                
            </Swiper>    
        </div>
    );
}

export default Hero;