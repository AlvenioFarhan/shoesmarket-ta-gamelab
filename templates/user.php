{% extends '/layout/master-user.html' %} {% block content %}

<!-- Navbar After Login -->
{% block navbar_afterLogin %} {% include '/user/navbar-afterLogin.html' %} {% endblock %}

<!-- carousel -->
{% block carousel %} {% include '/user/carousel.html' %} {% endblock %}

<!-- Content After Login -->
{% block content_afterLogin %} {% include '/user/content-afterLogin.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/user/footer.html' %} {% endblock %}

{% endblock %}
