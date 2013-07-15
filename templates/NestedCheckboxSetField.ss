<% if Options.Count %>
    <% loop Options %>
        <ul class="$extraClass">
            <li>
                $Title
                <% if Options.Count %>
                    <ul id="$ID" class="$extraClass">
                        <% loop Options %>
                            <li class="$Class">
                                <input id="$ID" class="checkbox" name="$Name" type="checkbox" value="$Value"<% if isChecked %> checked="checked"<% end_if %><% if isDisabled %> disabled="disabled"<% end_if %> />
                                <label for="$ID">$Title</label>
                            </li>
                        <% end_loop %>
                    </ul>
                <% else %>
                    <ul id="$ID" class="$extraClass">
                        <li>No options available</li>
                    </ul>
                <% end_if %>
            </li>
        </ul>
    <% end_loop %>
<% end_if %>