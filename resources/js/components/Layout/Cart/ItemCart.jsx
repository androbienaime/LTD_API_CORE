import React from "react";

const ItemCart = ({ item, removeButton, Quantity }) =>{
    return (
        <li class="flex py-6">
                      <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                        <img src={item.image} alt="Salmon orange fabric pouch with match zipper, gray zipper pull, and adjustable hip belt." class="h-full w-full object-cover object-center"/>
                      </div>

                      <div class="ml-4 flex flex-1 flex-col">
                        <div>
                          <div class="flex justify-between text-base font-medium text-gray-900">
                            <h3>
                              <a href="#">{ item.name }</a>
                            </h3>
                            <p class="ml-4">${ item.price }</p>
                          </div>
                          <p class="mt-1 text-sm text-gray-500">{ item.name }</p>
                        </div>
                        <div class="flex flex-1 items-end justify-between text-sm">
                          
                        <div class="sm:order-2">
                          { Quantity }
                        </div>

                          <div class="flex">
                            { removeButton }
                          </div>
                        </div>
                      </div>
                    </li>
    )
}

export default ItemCart;