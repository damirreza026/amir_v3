<style>
    .sale-card-link {
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

    .sale-card-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 35%;
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
        padding: 12px 12px 38px;
        background: linear-gradient(
            to top,
            rgba(0, 0, 0, 0.65),
            rgba(0, 0, 0, 0.15),
            transparent
        );
        pointer-events: none;
    }

    .sale-card-title {
        color: #ffffff;
        font-size: 18px;
        font-weight: 700;
        text-align: center;
        text-transform: capitalize;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
    }

    .sale-card-mobile-text {
        display: none;
        width: 100%;
        height: 100%;
        min-height: 0;
        align-items: center;
        justify-content: center;
        padding: 16px;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #222222;
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


<a
       href="{{ URL::signedRoute('profile') }}"
       target="_self" class="sale-card-link">
    <img
        src="{{ asset('img/PersonnelManagement/profile.jpg') }}"
        alt="Personnel Profile"
        class="sale-card-img"
    >

    <div class="sale-card-overlay">
        <span class="sale-card-title">اطلاعات پرسنل</span>
    </div>

    <div class="sale-card-mobile-text">
        Personnel Profile
    </div>
</a>
