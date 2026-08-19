<style>
    .recruitment-card-link {
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

    .recruitment-card-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 25%;
        transition: transform 0.3s ease;
    }

    .recruitment-card-link:hover .recruitment-card-img {
        transform: scale(1.05);
    }

    .recruitment-card-overlay {
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

    .recruitment-card-title {
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

    .recruitment-card-mobile-text {
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
        .recruitment-card-img,
        .recruitment-card-overlay,
        .recruitment-card-title {
            display: none;
        }

        .recruitment-card-mobile-text {
            display: flex;
        }
    }
</style>

<a
    href="{{ URL::signedRoute('recruitment') }}"
    target="_self"
    rel="noopener noreferrer"
    class="recruitment-card-link"
>
    <img
        src="{{ asset('img/PersonnelManagement/recruitment.jpg') }}"
        alt="Personnel Management"
        class="recruitment-card-img"
    >

    <div class="recruitment-card-overlay"></div>

    <span class="recruitment-card-title">
          استخدام پرسنل جدید
    </span>

    <div class="recruitment-card-mobile-text">
        Personnel Management
    </div>
</a>
