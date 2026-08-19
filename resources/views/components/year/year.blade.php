<style>
    .category-card-link {
        position: relative;
        display: block;
        width: 100%;
        height: 100%;
        min-height: 0;
        overflow: hidden;
        border-radius: 12px;
        text-decoration: none;
        background: #ffffff;
        cursor: pointer;
    }

    .category-card-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 25%;
        transition: transform 0.3s ease;
    }

    .category-card-link:hover .category-card-img {
        transform: scale(1.05);
    }

    .category-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to top,
            rgba(0, 0, 0, 0.70),
            rgba(0, 0, 0, 0.12),
            transparent
        );
        pointer-events: none;
    }

    .category-card-title {
        position: absolute;
        right: 12px;
        bottom: 25px;
        left: 12px;
        z-index: 2;
        margin: 0;
        color: #ffffff;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        text-align: center;
        text-transform: capitalize;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.95);
        pointer-events: none;
    }

    .category-card-mobile-text {
        display: none;
        width: 100%;
        height: 100%;
        min-height: 0;
        align-items: center;
        justify-content: center;
        padding: 16px;
        color: #222222;
        background: #f5f5f5;
        font-size: 18px;
        font-weight: 700;
        text-align: center;
    }

    @media (max-width: 640px) {
        .category-card-img,
        .category-card-overlay,
        .category-card-title {
            display: none;
        }

        .category-card-mobile-text {
            display: flex;
        }
    }
</style>

<a
    href="{{ URL::signedRoute('year') }}"
    target="_self"
    rel="noopener noreferrer"
    class="category-card-link"
>
    <img
        src="{{ asset('img/year/year.jpg') }}"
        alt="sale"
        class="category-card-img"
    >

    <div class="category-card-overlay"></div>

    <span class="category-card-title">
        افزودن سال و ماه و هفته مالی جدید
    </span>

    <div class="category-card-mobile-text">
        Category
    </div>
</a>
