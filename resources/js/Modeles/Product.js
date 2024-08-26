class Product{
    constructor(id, name, description, price, coverImage, images){
        this.id = id;
        this.name = name;
        this.description = description;
        this.price = price;
        this.coverImage = coverImage;
        this.images = images;
    }

    static fromApiResponse(response) {
        return new Product(
            response.id,
            response.name,
            response.description,
            response.price,
            response.coverImage,
            response.images
        );
    }
}
export default Product;
