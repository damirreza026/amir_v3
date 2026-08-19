<style>
    .sale-card-link {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
        min-height: 180px;
        overflow: hidden;
        border-radius: 12px;
        text-decoration: none;
        background: #ffffff;
        cursor: pointer;
    }

    .sale-card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }

    .sale-card-link:hover .sale-card-img {
        transform: scale(1.05);
    }

    .sale-card-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 12px;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.15), transparent);
    }

    .sale-card-title {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .sale-card-mobile-text {
        display: none;
        width: 100%;
        height: 100%;
        min-height: 180px;
        align-items: center;
        justify-content: center;
        padding: 16px;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #222;
        background: #f5f5f5;
        text-transform: capitalize;
    }

    @media (max-width: 640px) {
        .sale-card-img,
        .sale-card-overlay {
            display: none;
        }

        .sale-card-mobile-text {
            display: flex;
        }
    }
</style>


<a href="https://google.com" target="_self" class="sale-card-link">
    <img src="{{ asset('img/dashboard/invoice.jpg') }}" alt="today sale" class="sale-card-img">
    <div class="sale-card-overlay">
        <span class="sale-card-title">فاکتور ها</span>
    </div>
    <div class="sale-card-mobile-text">
        today sale
    </div>
</a>
