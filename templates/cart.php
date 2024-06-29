{% extends '/layout/master-user.html' %} {% block content %}

<!-- Navbar After Login 2 -->
{% block navbar_afterLogin2 %} {% include '/user/navbar-afterLogin2.html' %} {% endblock %}

<!-- Content Cart -->
{% block content_cart %} {% include '/user/content-cart.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/user/footer.html' %} {% endblock %}

{% endblock %}
