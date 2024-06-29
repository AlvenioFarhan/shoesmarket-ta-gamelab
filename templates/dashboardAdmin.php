{% extends '/layout/master-admin.html' %}
{% block content %}

<!-- navbar -->
{% block navbar %} {% include '/admin/navbar.html' %} {% endblock %}

<!-- data user -->
{% block data_user %} {% include '/admin/data-user.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/admin/footer.html' %} {% endblock %}

{% endblock %}
